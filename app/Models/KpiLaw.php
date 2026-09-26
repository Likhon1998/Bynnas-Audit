<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class KpiLaw extends Model
{
    public const OPERATION_ADD = 'add';

    public const OPERATION_SUBTRACT = 'subtract';

    public const OPERATION_DIVIDE = 'divide';

    protected $fillable = [
        'key',
        'label',
        'category',
        'operation',
        'left_operand',
        'right_operand',
        'formula_display',
        'description',
        'format',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Raw KPI / branch fields that may appear in a law formula.
     *
     * @return array<string, string>
     */
    public static function operandOptions(): array
    {
        return [
            'fo_count' => 'FO #',
            'total_samities' => 'Total Samities',
            'total_members' => 'Total Members',
            'total_borrowers' => 'Total Borrowers',
            'total_od_borrowers' => 'Total OD Borrowers',
            'fy_savings_collection' => 'FY Savings Collection',
            'fy_savings_withdrawal' => 'FY Savings Withdrawal',
            'savings_balance' => 'Savings Balance',
            'fy_members_admission' => 'FY Members Admission',
            'fy_members_dropout' => 'FY Members Dropout',
            'fy_disbursement_borrowers' => 'FY Disbursement Borrowers',
            'fy_fully_repayment_borrowers' => 'FY Fully Repayment Borrowers',
            'fy_disbursement_amount' => 'FY Disbursement Amount',
            'fy_loan_recovery' => 'FY Loan Recovery',
            'loan_outstanding' => 'Loan Outstanding',
            'recoverable' => 'Recoverable',
            'current_recovery' => 'Current Recovery',
            'due_recovery' => 'Due Recovery',
            'total_od_taka' => 'Total OD Taka',
            'due_loanee_loan_outstanding' => 'Due Loanee Loan Outstanding',
            'own_fund_until_prior_june' => 'Own Fund Until Prior June',
            'surplus_deficit_fy' => 'Surplus / Deficit (FY)',
            'new_due' => 'New Due',
            'due_increase_this_month' => 'Due Increase This Month',
        ];
    }

    /**
     * @return list<array{
     *   key:string,label:string,category:string,operation:string,
     *   left_operand:string,right_operand:string,formula_display:string,
     *   description:?string,format:string,sort_order:int
     * }>
     */
    public static function defaultDefinitions(): array
    {
        return [
            [
                'key' => 'fy_savings_increase',
                'label' => 'Fiscal year Savings Increase',
                'category' => 'increase',
                'operation' => self::OPERATION_SUBTRACT,
                'left_operand' => 'fy_savings_collection',
                'right_operand' => 'fy_savings_withdrawal',
                'formula_display' => 'FY Savings Collection − FY Savings Withdrawal',
                'description' => 'Net savings growth during the fiscal year.',
                'format' => 'money',
                'sort_order' => 10,
            ],
            [
                'key' => 'fy_members_increase',
                'label' => 'Fiscal year Members Increase',
                'category' => 'increase',
                'operation' => self::OPERATION_SUBTRACT,
                'left_operand' => 'fy_members_admission',
                'right_operand' => 'fy_members_dropout',
                'formula_display' => 'FY Members Admission − FY Members Dropout',
                'description' => 'Net member growth during the fiscal year.',
                'format' => 'int',
                'sort_order' => 20,
            ],
            [
                'key' => 'fy_borrowers_increase',
                'label' => 'Fiscal year Borrowers Increase',
                'category' => 'increase',
                'operation' => self::OPERATION_SUBTRACT,
                'left_operand' => 'fy_disbursement_borrowers',
                'right_operand' => 'fy_fully_repayment_borrowers',
                'formula_display' => 'FY Disbursement Borrowers − FY Fully Repayment Borrowers',
                'description' => 'Net borrower growth during the fiscal year.',
                'format' => 'int',
                'sort_order' => 30,
            ],
            [
                'key' => 'fy_loan_outstanding_increase',
                'label' => 'Fiscal year Loan Outstanding Increase',
                'category' => 'increase',
                'operation' => self::OPERATION_SUBTRACT,
                'left_operand' => 'fy_disbursement_amount',
                'right_operand' => 'fy_loan_recovery',
                'formula_display' => 'FY Disbursement Amount − FY Loan Recovery',
                'description' => 'Net change in loan outstanding from FY disbursement and recovery.',
                'format' => 'money',
                'sort_order' => 40,
            ],
            [
                'key' => 'total_surplus_deficit',
                'label' => 'Total Surplus / Deficit',
                'category' => 'fund',
                'operation' => self::OPERATION_ADD,
                'left_operand' => 'own_fund_until_prior_june',
                'right_operand' => 'surplus_deficit_fy',
                'formula_display' => 'Own Fund Until Prior June + Surplus / Deficit (FY)',
                'description' => 'Cumulative surplus or deficit including prior own fund.',
                'format' => 'money',
                'sort_order' => 50,
            ],
            [
                'key' => 'otr',
                'label' => 'OTR',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'current_recovery',
                'right_operand' => 'recoverable',
                'formula_display' => 'Current Recovery ÷ Recoverable',
                'description' => 'On-time recovery rate.',
                'format' => 'pct',
                'sort_order' => 60,
            ],
            [
                'key' => 'dr_borrowers',
                'label' => 'DR (Borrowers)',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'total_od_borrowers',
                'right_operand' => 'total_borrowers',
                'formula_display' => 'Total OD Borrowers ÷ Total Borrowers',
                'description' => 'Delinquency rate by borrower count.',
                'format' => 'pct',
                'sort_order' => 70,
            ],
            [
                'key' => 'dr_taka',
                'label' => 'DR (Taka)',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'total_od_taka',
                'right_operand' => 'loan_outstanding',
                'formula_display' => 'Total OD Taka ÷ Loan Outstanding',
                'description' => 'Delinquency rate by amount.',
                'format' => 'pct',
                'sort_order' => 80,
            ],
            [
                'key' => 'par',
                'label' => 'PAR',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'due_loanee_loan_outstanding',
                'right_operand' => 'loan_outstanding',
                'formula_display' => 'Due Loanee Loan Outstanding ÷ Loan Outstanding',
                'description' => 'Portfolio at risk.',
                'format' => 'pct',
                'sort_order' => 90,
            ],
            [
                'key' => 'overdue_growth_vs_outstanding',
                'label' => 'Overdue Growth Rate vs. Outstanding Loans',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'due_increase_this_month',
                'right_operand' => 'loan_outstanding',
                'formula_display' => 'Due Increase This Month ÷ Loan Outstanding',
                'description' => 'Overdue growth relative to outstanding loans.',
                'format' => 'pct',
                'sort_order' => 100,
            ],
            [
                'key' => 'due_recovery_pct',
                'label' => 'Due Recovery %',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'due_recovery',
                'right_operand' => 'total_od_taka',
                'formula_display' => 'Due Recovery ÷ Total OD Taka',
                'description' => 'Share of overdue amount recovered.',
                'format' => 'pct',
                'sort_order' => 110,
            ],
            [
                'key' => 'member_loanee',
                'label' => 'Member : Loanee',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'total_borrowers',
                'right_operand' => 'total_members',
                'formula_display' => 'Total Borrowers ÷ Total Members',
                'description' => 'Share of members who are borrowers.',
                'format' => 'pct',
                'sort_order' => 120,
            ],
            [
                'key' => 'savings_loan',
                'label' => 'Savings : Loan Outstanding',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'savings_balance',
                'right_operand' => 'loan_outstanding',
                'formula_display' => 'Savings Balance ÷ Loan Outstanding',
                'description' => 'Savings coverage of loan outstanding.',
                'format' => 'pct',
                'sort_order' => 130,
            ],
            [
                'key' => 'dropout_pct',
                'label' => 'Dropout %',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'fy_members_dropout',
                'right_operand' => 'fy_members_admission',
                'formula_display' => 'FY Members Dropout ÷ FY Members Admission',
                'description' => 'Dropout relative to new admissions.',
                'format' => 'pct',
                'sort_order' => 140,
            ],
            [
                'key' => 'savings_withdrawal_pct',
                'label' => 'Savings Withdrawal %',
                'category' => 'percentage',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'fy_savings_withdrawal',
                'right_operand' => 'fy_savings_collection',
                'formula_display' => 'FY Savings Withdrawal ÷ FY Savings Collection',
                'description' => 'Withdrawal relative to savings collection.',
                'format' => 'pct',
                'sort_order' => 150,
            ],
            [
                'key' => 'samities_member',
                'label' => 'Samities : Member',
                'category' => 'ratio',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'total_members',
                'right_operand' => 'total_samities',
                'formula_display' => 'Total Members ÷ Total Samities',
                'description' => 'Average members per samity.',
                'format' => 'ratio',
                'sort_order' => 160,
            ],
            [
                'key' => 'samities_borrowers',
                'label' => 'Samities : Borrowers',
                'category' => 'ratio',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'total_borrowers',
                'right_operand' => 'total_samities',
                'formula_display' => 'Total Borrowers ÷ Total Samities',
                'description' => 'Average borrowers per samity.',
                'format' => 'ratio',
                'sort_order' => 170,
            ],
            [
                'key' => 'fo_member',
                'label' => 'FO : Member',
                'category' => 'ratio',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'total_members',
                'right_operand' => 'fo_count',
                'formula_display' => 'Total Members ÷ FO #',
                'description' => 'Average members per field officer.',
                'format' => 'ratio',
                'sort_order' => 180,
            ],
            [
                'key' => 'fo_borrowers',
                'label' => 'FO : Borrowers',
                'category' => 'ratio',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'total_borrowers',
                'right_operand' => 'fo_count',
                'formula_display' => 'Total Borrowers ÷ FO #',
                'description' => 'Average borrowers per field officer.',
                'format' => 'ratio',
                'sort_order' => 190,
            ],
            [
                'key' => 'fo_savings',
                'label' => 'FO : Savings Balance',
                'category' => 'ratio',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'savings_balance',
                'right_operand' => 'fo_count',
                'formula_display' => 'Savings Balance ÷ FO #',
                'description' => 'Average savings balance per field officer.',
                'format' => 'money',
                'sort_order' => 200,
            ],
            [
                'key' => 'fo_loan',
                'label' => 'FO : Loan Outstanding',
                'category' => 'ratio',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'loan_outstanding',
                'right_operand' => 'fo_count',
                'formula_display' => 'Loan Outstanding ÷ FO #',
                'description' => 'Average loan outstanding per field officer.',
                'format' => 'money',
                'sort_order' => 210,
            ],
            [
                'key' => 'member_savings',
                'label' => 'Member : Savings Balance',
                'category' => 'ratio',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'savings_balance',
                'right_operand' => 'total_members',
                'formula_display' => 'Savings Balance ÷ Total Members',
                'description' => 'Average savings per member.',
                'format' => 'money',
                'sort_order' => 220,
            ],
            [
                'key' => 'borrowers_loan',
                'label' => 'Borrowers : Loan Outstanding',
                'category' => 'ratio',
                'operation' => self::OPERATION_DIVIDE,
                'left_operand' => 'loan_outstanding',
                'right_operand' => 'total_borrowers',
                'formula_display' => 'Loan Outstanding ÷ Total Borrowers',
                'description' => 'Average loan outstanding per borrower.',
                'format' => 'money',
                'sort_order' => 230,
            ],
        ];
    }

    public static function ensureDefaults(): void
    {
        if (static::query()->exists()) {
            return;
        }

        foreach (static::defaultDefinitions() as $definition) {
            static::query()->create($definition + ['is_active' => true]);
        }
    }

    public static function resetToDefaults(): void
    {
        static::query()->delete();
        foreach (static::defaultDefinitions() as $definition) {
            static::query()->create($definition + ['is_active' => true]);
        }
    }

    /**
     * @return Collection<int, static>
     */
    public static function ordered(): Collection
    {
        static::ensureDefaults();

        return static::query()->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * @return Collection<int, static>
     */
    public static function activeOrdered(): Collection
    {
        return static::ordered()->where('is_active', true)->values();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function rebuildFormulaDisplay(): string
    {
        $operands = static::operandOptions();
        $left = $operands[$this->left_operand] ?? $this->left_operand;
        $right = $operands[$this->right_operand] ?? $this->right_operand;
        $op = match ($this->operation) {
            self::OPERATION_ADD => '+',
            self::OPERATION_SUBTRACT => '−',
            default => '÷',
        };

        return "{$left} {$op} {$right}";
    }

    /**
     * A short sentence a person can read, plus a worked example with sample numbers.
     *
     * @return array{sentence:string,left_label:string,right_label:string,left_value:string,right_value:string,symbol:string,result:string,note:string}
     */
    public function workedExample(): array
    {
        $operands = static::operandOptions();
        $leftLabel = $operands[$this->left_operand] ?? $this->left_operand;
        $rightLabel = $operands[$this->right_operand] ?? $this->right_operand;
        $left = (float) (static::sampleValues()[$this->left_operand] ?? 100);
        $right = (float) (static::sampleValues()[$this->right_operand] ?? 10);

        $symbol = match ($this->operation) {
            self::OPERATION_ADD => '+',
            self::OPERATION_SUBTRACT => '−',
            default => '÷',
        };

        $raw = match ($this->operation) {
            self::OPERATION_ADD => $left + $right,
            self::OPERATION_SUBTRACT => $left - $right,
            default => abs($right) < 0.0000001 ? null : $left / $right,
        };

        $sentence = match ($this->operation) {
            self::OPERATION_ADD => "Add {$leftLabel} and {$rightLabel}.",
            self::OPERATION_SUBTRACT => "Start with {$leftLabel}, then subtract {$rightLabel}.",
            default => "Divide {$leftLabel} by {$rightLabel}.",
        };

        if ($raw === null) {
            $result = 'Cannot divide by zero';
            $note = 'The second number must not be zero.';
        } elseif ($this->format === 'pct') {
            $result = number_format($raw * 100, 2).'%';
            $note = 'The division is '.number_format($raw, 4).'. Excel shows that as a percentage, so this example is '.$result.'.';
        } elseif ($this->format === 'int') {
            $result = number_format((int) round($raw));
            $note = 'The result is a whole number.';
        } elseif ($this->format === 'money') {
            $result = number_format($raw, 2);
            $note = 'The result is an amount in taka.';
        } else {
            $result = number_format($raw, 2);
            $note = 'The result is how many times the second number fits into the first.';
        }

        return [
            'sentence' => $sentence,
            'left_label' => $leftLabel,
            'right_label' => $rightLabel,
            'left_value' => $this->formatSample($left),
            'right_value' => $this->formatSample($right),
            'symbol' => $symbol,
            'result' => $result,
            'note' => $note,
        ];
    }

    protected function formatSample(float $value): string
    {
        return fmod($value, 1.0) === 0.0
            ? number_format($value)
            : number_format($value, 2);
    }

    /**
     * @return array<string, float|int>
     */
    public static function sampleValues(): array
    {
        return [
            'fo_count' => 5,
            'total_samities' => 40,
            'total_members' => 1200,
            'total_borrowers' => 900,
            'total_od_borrowers' => 45,
            'fy_savings_collection' => 1500000,
            'fy_savings_withdrawal' => 300000,
            'savings_balance' => 800000,
            'fy_members_admission' => 200,
            'fy_members_dropout' => 40,
            'fy_disbursement_borrowers' => 250,
            'fy_fully_repayment_borrowers' => 80,
            'fy_disbursement_amount' => 4000000,
            'fy_loan_recovery' => 3200000,
            'loan_outstanding' => 5000000,
            'recoverable' => 1000000,
            'current_recovery' => 920000,
            'due_recovery' => 80000,
            'total_od_taka' => 250000,
            'due_loanee_loan_outstanding' => 400000,
            'own_fund_until_prior_june' => 2000000,
            'surplus_deficit_fy' => 150000,
            'new_due' => 50000,
            'due_increase_this_month' => 20000,
        ];
    }
}
