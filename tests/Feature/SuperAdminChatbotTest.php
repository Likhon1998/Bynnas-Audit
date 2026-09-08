<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\ShakhaEmployee;
use App\Models\SuperAdminChatMessage;
use App\Models\User;
use App\Services\GeminiChatService;
use App\Services\SuperAdminChatContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class SuperAdminChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.key' => 'test-gemini-key']);
    }

    public function test_chatbot_widget_is_visible_only_on_super_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create(['is_superadmin' => true, 'is_active' => true, 'email_verified_at' => now()]);
        $regularUser = User::factory()->create(['is_superadmin' => false, 'is_active' => true, 'email_verified_at' => now()]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Bynnas Audit Assistant');

        $this->actingAs($regularUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Bynnas Audit Assistant');
    }

    public function test_non_super_admin_cannot_access_chatbot_routes(): void
    {
        $user = User::factory()->create(['is_superadmin' => false, 'is_active' => true, 'email_verified_at' => now()]);

        $this->actingAs($user)
            ->getJson(route('superadmin.chat.history', ['thread_uuid' => Str::uuid()]))
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson(route('superadmin.chat.ask'), ['thread_uuid' => (string) Str::uuid(), 'message' => 'Show reports'])
            ->assertForbidden();
    }

    public function test_super_admin_receives_grounded_answer_and_encrypted_history_is_saved(): void
    {
        $user = User::factory()->create(['is_superadmin' => true, 'is_active' => true, 'email_verified_at' => now()]);
        $thread = (string) Str::uuid();
        Http::fakeSequence()
            ->push($this->geminiResponse('{"intent":"ops.overview","filters":{"month":null,"year":null,"fy":null,"shakha":null,"status":null,"search":null}}'))
            ->push($this->geminiResponse('There are 5 completed reports.'));

        $this->actingAs($user)
            ->postJson(route('superadmin.chat.ask'), ['thread_uuid' => $thread, 'message' => 'How many reports?'])
            ->assertOk()
            ->assertJsonPath('intent', 'ops.overview')
            ->assertJsonPath('answer', 'There are 5 completed reports.');

        $record = SuperAdminChatMessage::query()->firstOrFail();
        $this->assertSame('How many reports?', $record->question);
        $this->assertSame('There are 5 completed reports.', $record->answer);
        $this->assertNotSame('How many reports?', DB::table('super_admin_chat_messages')->value('question'));

        $this->actingAs($user)
            ->getJson(route('superadmin.chat.history', ['thread_uuid' => $thread]))
            ->assertOk()
            ->assertJsonPath('messages.0.question', 'How many reports?');
    }

    public function test_typo_tolerant_employee_question_uses_only_curated_directory_fields(): void
    {
        $user = User::factory()->create(['is_superadmin' => true, 'is_active' => true, 'email_verified_at' => now()]);
        $area = Area::query()->create(['name' => 'Dhaka Area', 'division' => 'Dhaka']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Mirpur Shakha', 'code' => 'BYN-001', 'status' => 'active']);
        ShakhaEmployee::query()->create([
            'shakha_id' => $shakha->id,
            'employee_code' => 'EMP-01',
            'name' => 'Rahim Uddin',
            'designation' => 'Manager',
            'phone' => '01700000000',
            'email' => 'rahim@example.com',
            'status' => 'active',
        ]);

        Http::fakeSequence()
            ->push($this->geminiResponse('{"intent":"employees.directory","filters":{"month":null,"year":null,"fy":null,"shakha":"Mirpur","status":null,"search":null,"include_contacts":true}}'))
            ->push($this->geminiResponse('Rahim Uddin is the Manager at Mirpur Shakha.'));

        $this->actingAs($user)->postJson(route('superadmin.chat.ask'), [
            'thread_uuid' => (string) Str::uuid(),
            'message' => 'mirpur shaka employe contct dekhao',
        ])->assertOk()->assertJsonPath('intent', 'employees.directory');

        $requests = Http::recorded();
        $answerPayload = $requests[1][0]->data();
        $encoded = json_encode($answerPayload, JSON_UNESCAPED_UNICODE);
        $databaseContext = (string) data_get($answerPayload, 'contents.0.parts.0.text');
        $databaseContext = str($databaseContext)->between("DATABASE_CONTEXT:\n", "\n\nUSER_QUESTION:")->toString();
        $this->assertStringContainsString('Rahim Uddin', $encoded);
        $this->assertStringContainsString('rahim@example.com', $encoded);
        $this->assertStringNotContainsString('password', strtolower($databaseContext));
        $this->assertStringNotContainsString('pages_data', strtolower($databaseContext));
        $this->assertStringNotContainsString('photo_path', strtolower($databaseContext));
    }

    public function test_complex_question_builds_multiple_contexts_and_uses_recent_history(): void
    {
        Http::fakeSequence()
            ->push($this->geminiResponse('{"requests":[{"intent":"reports.status","filters":{"month":8,"year":2026,"fy":null,"shakha":"Mirpur","status":"completed","search":null,"period_scope":"current"}},{"intent":"risk.summary","filters":{"month":8,"year":2026,"fy":null,"shakha":"Mirpur","status":null,"search":null,"period_scope":"current"}}]}'))
            ->push($this->geminiResponse('Mirpur completed reports and risk comparison.'));

        $result = app(GeminiChatService::class)->ask(
            'Compare those completed reports with its risk and explain the difference.',
            [['question' => 'Show Mirpur activity for August 2026.', 'answer' => 'Mirpur activity was displayed.']]
        );

        $this->assertSame('reports.status', $result['intent']);
        $this->assertSame(['reports.status', 'risk.summary'], $result['intents']);

        $requests = Http::recorded();
        $classificationPrompt = (string) data_get($requests[0][0]->data(), 'contents.0.parts.0.text');
        $answerPrompt = (string) data_get($requests[1][0]->data(), 'contents.0.parts.0.text');
        $this->assertStringContainsString('Show Mirpur activity for August 2026.', $classificationPrompt);
        $this->assertStringContainsString('"intent":"reports.status"', $answerPrompt);
        $this->assertStringContainsString('"intent":"risk.summary"', $answerPrompt);
    }

    public function test_employee_contacts_are_omitted_unless_explicitly_requested(): void
    {
        $area = Area::query()->create(['name' => 'Dhaka Area', 'division' => 'Dhaka']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Mirpur', 'code' => 'MIR-1', 'status' => 'active']);
        ShakhaEmployee::query()->create([
            'shakha_id' => $shakha->id,
            'employee_code' => 'EMP-1',
            'name' => 'Employee One',
            'designation' => 'Manager',
            'phone' => '01700000000',
            'email' => 'employee@example.com',
            'status' => 'active',
        ]);

        $context = app(SuperAdminChatContextService::class)->build('employees.directory', [
            'shakha' => 'Mirpur',
            'include_contacts' => false,
        ]);

        $this->assertArrayNotHasKey('phone', $context['employees'][0]);
        $this->assertArrayNotHasKey('email', $context['employees'][0]);
    }

    public function test_kpi_totals_cover_all_matches_when_detail_rows_are_limited(): void
    {
        config(['services.gemini.max_rows' => 5]);
        $area = Area::query()->create(['name' => 'Dhaka Area', 'division' => 'Dhaka']);

        foreach (range(1, 6) as $index) {
            $shakha = Shakha::query()->create([
                'area_id' => $area->id,
                'name' => "Shakha {$index}",
                'code' => "SHA-{$index}",
                'status' => 'active',
            ]);
            ShakhaAnnualKpi::query()->create([
                'shakha_id' => $shakha->id,
                'fy_label' => '2026-2027',
                'total_members' => 100,
                'loan_outstanding' => 1000,
            ]);
        }

        $context = app(SuperAdminChatContextService::class)->build('kpi.summary', ['fy' => '2026-2027']);

        $this->assertSame(6, $context['shakhas_with_kpi']);
        $this->assertSame(5, $context['rows_returned']);
        $this->assertSame(600, $context['totals']['members']);
        $this->assertSame(6000.0, $context['totals']['loan_outstanding']);
    }

    public function test_context_sanitizer_removes_prohibited_nested_fields(): void
    {
        $clean = app(SuperAdminChatContextService::class)->sanitize([
            'safe' => 4,
            'password' => 'secret',
            'nested' => ['pages_data' => ['private'], 'name' => 'Allowed'],
        ]);

        $this->assertSame(['safe' => 4, 'nested' => ['name' => 'Allowed']], $clean);
    }

    public function test_every_supported_intent_builds_context_safely(): void
    {
        $service = app(SuperAdminChatContextService::class);

        foreach (SuperAdminChatContextService::INTENTS as $intent) {
            $this->assertIsArray($service->build($intent, []), "Context failed for {$intent}");
        }
    }

    public function test_invalid_chat_input_is_rejected(): void
    {
        $user = User::factory()->create(['is_superadmin' => true, 'is_active' => true, 'email_verified_at' => now()]);

        $this->actingAs($user)->postJson(route('superadmin.chat.ask'), [
            'thread_uuid' => 'invalid',
            'message' => 'x',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['thread_uuid', 'message']);

        Http::assertNothingSent();
    }

    public function test_unexpected_failure_returns_a_safe_audited_response(): void
    {
        $user = User::factory()->create(['is_superadmin' => true, 'is_active' => true, 'email_verified_at' => now()]);
        $chat = Mockery::mock(GeminiChatService::class);
        $chat->shouldReceive('ask')->once()->andThrow(new RuntimeException('Sensitive internal failure details'));
        $this->app->instance(GeminiChatService::class, $chat);

        $this->actingAs($user)->postJson(route('superadmin.chat.ask'), [
            'thread_uuid' => (string) Str::uuid(),
            'message' => 'Show audit summary',
        ])->assertStatus(503)
            ->assertJsonPath('message', 'The assistant could not complete this request. Please try again.')
            ->assertJsonMissing(['message' => 'Sensitive internal failure details']);

        $this->assertDatabaseHas('super_admin_chat_messages', [
            'user_id' => $user->id,
            'status' => 'failed',
        ]);
    }

    public function test_gemini_configuration_failure_is_audited_and_returns_safe_error(): void
    {
        $user = User::factory()->create(['is_superadmin' => true, 'is_active' => true, 'email_verified_at' => now()]);
        config(['services.gemini.key' => null]);

        $this->actingAs($user)->postJson(route('superadmin.chat.ask'), [
            'thread_uuid' => (string) Str::uuid(),
            'message' => 'Show database overview',
        ])->assertStatus(503)->assertJsonPath('message', 'Gemini is not configured. Add GEMINI_API_KEY to the server environment.');

        $this->assertDatabaseHas('super_admin_chat_messages', ['user_id' => $user->id, 'status' => 'failed']);
        Http::assertNothingSent();
    }

    /** @return array<string,mixed> */
    private function geminiResponse(string $text): array
    {
        return ['candidates' => [['content' => ['parts' => [['text' => $text]]]]]];
    }
}
