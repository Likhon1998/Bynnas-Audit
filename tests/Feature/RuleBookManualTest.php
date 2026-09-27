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
                    ['statement' => 'A member may adjust a current loan only when savings cover the outstanding amount.', 'article' => '1', 'where' => '', 'when' => '', 'who' => ''],
                    ['statement' => '   ', 'article' => '', 'where' => '', 'when' => '', 'who' => ''],
                    ['statement' => 'Closing the loan without full savings cover is not permitted.', 'article' => '2', 'where' => '', 'when' => '2024', 'who' => 'Branch manager'],
                ],
            ])
            ->assertRedirect(route('rule-book.index'));

        $this->assertSame(2, Rule::query()->count());

        $first = Rule::query()->orderBy('serial')->first();
        $this->assertSame('1', $first->article);
        $this->assertSame('Current Loan Adjustment Policy', $first->reference_where);

        $this->actingAs($user)
            ->put(route('rule-book.update', $first), [
                'statement' => 'Updated rule text for the first clause.',
                'article' => '1',
                'where' => 'Head office circular',
                'when' => '',
                'who' => '',
                'source_name' => 'Current Loan Adjustment Policy',
            ])
            ->assertRedirect();

        $this->assertSame('Updated rule text for the first clause.', $first->fresh()->statement);
        $this->assertSame('Head office circular', $first->fresh()->reference_where);
    }
}
