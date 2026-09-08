<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Models\SuperAdminChatMessage;
use App\Models\User;
use App\Services\SuperAdminChatContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
            ->push($this->geminiResponse('{"intent":"employees.directory","filters":{"month":null,"year":null,"fy":null,"shakha":"Mirpur","status":null,"search":null}}'))
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

    public function test_context_sanitizer_removes_prohibited_nested_fields(): void
    {
        $clean = app(SuperAdminChatContextService::class)->sanitize([
            'safe' => 4,
            'password' => 'secret',
            'nested' => ['pages_data' => ['private'], 'name' => 'Allowed'],
        ]);

        $this->assertSame(['safe' => 4, 'nested' => ['name' => 'Allowed']], $clean);
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
