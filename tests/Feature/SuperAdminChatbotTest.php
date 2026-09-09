<?php

namespace Tests\Feature;

use App\Models\ActivityType;
use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\Employee;
use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\Position;
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

    public function test_visits_context_lists_shakhas_allocated_to_a_named_visitor(): void
    {
        $now = now('Asia/Dhaka');
        $position = Position::query()->create([
            'serial' => 1,
            'title' => 'Audit Officer',
            'slug' => 'ao-chat-alloc',
            'color' => '#4C6FFF',
        ]);
        $officer = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Shahidul Alam',
            'sort_order' => 1,
        ]);
        $area = Area::query()->create(['name' => 'Dhaka Area', 'division' => 'Dhaka']);
        $mirpur = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Mirpur Shakha', 'code' => 'MIR-1', 'status' => 'active']);
        $bhola = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Bhola Branch', 'code' => 'BHO-1', 'status' => 'active']);
        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-chat-alloc',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->create([
            'name' => 'FY '.$now->year.'-'.($now->year + 1),
            'fy_label' => $now->year.'-'.($now->year + 1),
            'start_date' => $now->copy()->startOfYear()->toDateString(),
            'end_date' => $now->copy()->endOfYear()->toDateString(),
            'status' => 'active',
            'generated_at' => $now,
        ]);

        foreach ([$mirpur, $bhola] as $index => $shakha) {
            $item = MonthlyWorkItem::query()->create([
                'audit_plan_id' => $plan->id,
                'fy_label' => $plan->fy_label,
                'month_index' => (int) $now->month,
                'category' => 'shakha_audit',
                'activity_type_id' => $activity->id,
                'schedulable_type' => Shakha::class,
                'schedulable_id' => $shakha->id,
                'source' => MonthlyWorkItem::SOURCE_YEARLY,
                'status' => MonthlyWorkItem::STATUS_ASSIGNED,
                'entity_label' => $shakha->name,
            ]);
            $assignment = MonthlyAssignment::query()->create([
                'monthly_work_item_id' => $item->id,
                'employee_id' => $officer->id,
                'start_date' => $now->copy()->startOfMonth()->addDays($index + 1)->toDateString(),
                'end_date' => $now->copy()->startOfMonth()->addDays($index + 3)->toDateString(),
                'duration_days' => 3,
            ]);
            $assignment->visitors()->sync([$officer->id => ['sort_order' => 0]]);
        }

        $context = app(SuperAdminChatContextService::class)->build('visits.performance', [
            'search' => 'Shahidul Islam',
            'month' => (int) $now->month,
            'year' => (int) $now->year,
        ]);

        $this->assertSame(2, $context['assignment_count']);
        $this->assertSame(['Shahidul Alam'], $context['matched_visitors']);
        $this->assertSame('Shahidul Alam', $context['by_visitor'][0]['visitor']);
        $this->assertEqualsCanonicalizing(['Mirpur Shakha', 'Bhola Branch'], $context['by_visitor'][0]['shakhas']);
    }

    public function test_visitor_allocation_question_sends_shakha_names_to_gemini(): void
    {
        $now = now('Asia/Dhaka');
        $position = Position::query()->create([
            'serial' => 2,
            'title' => 'Audit Officer',
            'slug' => 'ao-chat-alloc-ask',
            'color' => '#4C6FFF',
        ]);
        $officer = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Shahidul Alam',
            'sort_order' => 1,
        ]);
        $area = Area::query()->create(['name' => 'Barishal Area', 'division' => 'Barishal']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Bhola Branch 2', 'code' => 'BHO-2', 'status' => 'active']);
        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-chat-alloc-ask',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->create([
            'name' => 'FY plan',
            'fy_label' => '2026-2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'generated_at' => $now,
        ]);
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $plan->fy_label,
            'month_index' => (int) $now->month,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_ASSIGNED,
            'entity_label' => $shakha->name,
        ]);
        $assignment = MonthlyAssignment::query()->create([
            'monthly_work_item_id' => $item->id,
            'employee_id' => $officer->id,
            'start_date' => $now->toDateString(),
            'end_date' => $now->copy()->addDays(2)->toDateString(),
            'duration_days' => 3,
        ]);
        $assignment->visitors()->sync([$officer->id => ['sort_order' => 0]]);

        Http::fakeSequence()
            ->push($this->geminiResponse('{"intent":"visits.performance","filters":{"month":null,"year":null,"fy":null,"shakha":null,"status":null,"search":"Shahidul Islam","period_scope":"current"}}'))
            ->push($this->geminiResponse('Shahidul Alam ei month Bhola Branch 2 e allocate kora hoyeche.'));

        $result = app(GeminiChatService::class)->ask(
            'shahidul islam k ai month konkon shakhay allocate kora hoyeche ?'
        );

        $this->assertSame('visits.performance', $result['intent']);
        $answerPrompt = (string) data_get(Http::recorded()[1][0]->data(), 'contents.0.parts.0.text');
        $this->assertStringContainsString('Bhola Branch 2', $answerPrompt);
        $this->assertStringContainsString('Shahidul Alam', $answerPrompt);
    }

    public function test_allocation_question_is_forced_to_visits_even_when_gemini_picks_the_wrong_intent(): void
    {
        $now = now('Asia/Dhaka');
        $position = Position::query()->create([
            'serial' => 3,
            'title' => 'Audit Officer',
            'slug' => 'ao-chat-alloc-override',
            'color' => '#4C6FFF',
        ]);
        $officer = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Shahidul Alam',
            'sort_order' => 1,
        ]);
        $area = Area::query()->create(['name' => 'Dhaka Area', 'division' => 'Dhaka']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Jatrabari Shakha', 'code' => 'BYN-014', 'status' => 'active']);
        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-chat-alloc-override',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->create([
            'name' => 'FY plan override',
            'fy_label' => '2026-2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'generated_at' => $now,
        ]);
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $plan->fy_label,
            'month_index' => (int) $now->month,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_ASSIGNED,
            'entity_label' => $shakha->name,
        ]);
        $assignment = MonthlyAssignment::query()->create([
            'monthly_work_item_id' => $item->id,
            'employee_id' => $officer->id,
            'start_date' => $now->toDateString(),
            'end_date' => $now->copy()->addDays(2)->toDateString(),
            'duration_days' => 3,
        ]);
        $assignment->visitors()->sync([$officer->id => ['sort_order' => 0]]);

        Http::fakeSequence()
            ->push($this->geminiResponse('{"intent":"employees.directory","filters":{"month":null,"year":null,"fy":null,"shakha":null,"status":null,"search":"Shahidul Islam","period_scope":"current"}}'))
            ->push($this->geminiResponse('Shahidul Alam ei month Jatrabari Shakha te allocate.'));

        $result = app(GeminiChatService::class)->ask(
            'shahidul islam k ai month konkon shakhay allocate kora hoyeche ?',
            [['question' => 'previous', 'answer' => 'The information is unavailable.']]
        );

        $this->assertSame('visits.performance', $result['intent']);
        $answerPrompt = (string) data_get(Http::recorded()[1][0]->data(), 'contents.0.parts.0.text');
        $this->assertStringContainsString('Jatrabari Shakha', $answerPrompt);
        $this->assertStringContainsString('Shahidul Alam', $answerPrompt);
        $this->assertStringContainsString('"intent":"visits.performance"', $answerPrompt);
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
