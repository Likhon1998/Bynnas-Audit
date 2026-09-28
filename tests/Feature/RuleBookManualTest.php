<?php

namespace Tests\Feature;

use App\Models\Rule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleBookManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_writes_rules_and_saves_them(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('rule-book.index'))
            ->assertOk()
            ->assertSee('Write the rule here')
            ->assertSee('Save rules')
            ->assertDontSee('Read PDF');

        $this->actingAs($user)
            ->post(route('rule-book.store'), [
                'source_name' => 'Current Loan Adjustment Policy',
                'rules' => [
                    ['statement' => 'A member may adjust a current loan only when savings cover the outstanding amount.', 'article' => '1'],
                    ['statement' => '   ', 'article' => ''],
                    ['statement' => 'Closing the loan without full savings cover is not permitted.', 'article' => '2'],
                ],
            ])
            ->assertRedirect(route('rule-book.index'));

        $this->assertSame(2, Rule::query()->count());

        $first = Rule::query()->orderBy('serial')->first();
        $this->assertSame('1', $first->article);
        $this->assertSame('Current Loan Adjustment Policy', $first->source_name);
        $this->assertSame('', $first->reference_where);

        $this->actingAs($user)
            ->put(route('rule-book.update', $first), [
                'statement' => 'Updated rule text for the first clause.',
                'article' => '1',
                'source_name' => 'Current Loan Adjustment Policy',
            ])
            ->assertRedirect();

        $this->assertSame('Updated rule text for the first clause.', $first->fresh()->statement);
        $this->assertSame('Current Loan Adjustment Policy', $first->fresh()->source_name);
    }

    public function test_a_new_criteria_line_can_be_added_to_the_rule_book(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('rule-book.quick'), [
                'statement' => 'A newly written criteria line from the report.',
            ])
            ->assertOk()
            ->assertJson([
                'added' => true,
                'group' => 'No document',
                'value' => 'A newly written criteria line from the report.',
            ]);

        $this->actingAs($user)
            ->postJson(route('rule-book.quick'), [
                'statement' => 'A newly written criteria line from the report.',
            ])
            ->assertOk()
            ->assertJson(['added' => false]);

        $this->actingAs($user)
            ->postJson(route('rule-book.quick'), [
                'statement' => 'Loans must be adjusted within the month.',
                'article' => '১৪',
                'source_name' => 'Current Loan Adjustment Policy',
            ])
            ->assertOk()
            ->assertJson([
                'added' => true,
                'group' => 'Current Loan Adjustment Policy',
                'article' => '১৪',
                'value' => 'Loans must be adjusted within the month.',
            ]);

        $this->assertSame(2, Rule::query()->count());
    }

    public function test_criteria_picker_is_a_search_field(): void
    {
        $html = view('rule-book.partials.criteria-picker', [
            'ruleBookRules' => collect(),
        ])->render();

        $this->assertStringContainsString('প্রচলিত নিয়ম বাছাই করুন', $html);
        $this->assertStringContainsString('খুঁজুন', $html);
        $this->assertStringNotContainsString('<select', $html);
    }
}
