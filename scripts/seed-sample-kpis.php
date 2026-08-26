<?php

use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Support\FinancialYear;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fyLabel = FinancialYear::current()->label;

$samples = [
    [
        'id' => 1,
        'opening_date' => '2018-03-15',
        'focal_person_name' => 'Md. Rafiqul Islam',
        'kpi' => [
            'fo_count' => 4, 'total_samities' => 86, 'total_members' => 1840, 'total_borrowers' => 1520, 'total_od_borrowers' => 95,
            'fy_members_admission' => 210, 'fy_members_dropout' => 88, 'fy_disbursement_borrowers' => 340, 'fy_fully_repayment_borrowers' => 290,
            'fy_savings_collection' => 12500000, 'fy_savings_withdrawal' => 9800000, 'savings_balance' => 34200000,
            'fy_disbursement_amount' => 48500000, 'fy_loan_recovery' => 45200000, 'loan_outstanding' => 61200000,
            'recoverable' => 8200000, 'current_recovery' => 7950000, 'due_recovery' => 980000,
            'total_od_taka' => 2450000, 'due_loanee_loan_outstanding' => 3100000,
            'own_fund_until_prior_june' => 1850000, 'surplus_deficit_fy' => 420000, 'new_due' => 310000, 'due_increase_this_month' => 85000,
        ],
    ],
    [
        'id' => 2,
        'opening_date' => '2016-07-01',
        'focal_person_name' => 'Md. Rafiqul Islam',
        'kpi' => [
            'fo_count' => 5, 'total_samities' => 102, 'total_members' => 2210, 'total_borrowers' => 1880, 'total_od_borrowers' => 72,
            'fy_members_admission' => 265, 'fy_members_dropout' => 110, 'fy_disbursement_borrowers' => 410, 'fy_fully_repayment_borrowers' => 355,
            'fy_savings_collection' => 15800000, 'fy_savings_withdrawal' => 11200000, 'savings_balance' => 41800000,
            'fy_disbursement_amount' => 56200000, 'fy_loan_recovery' => 53100000, 'loan_outstanding' => 70500000,
            'recoverable' => 9100000, 'current_recovery' => 8900000, 'due_recovery' => 760000,
            'total_od_taka' => 1980000, 'due_loanee_loan_outstanding' => 2550000,
            'own_fund_until_prior_june' => 2450000, 'surplus_deficit_fy' => 610000, 'new_due' => 185000, 'due_increase_this_month' => -42000,
        ],
    ],
    [
        'id' => 3,
        'opening_date' => '2019-11-20',
        'focal_person_name' => 'Sumon Kumar Halder',
        'kpi' => [
            'fo_count' => 3, 'total_samities' => 64, 'total_members' => 1320, 'total_borrowers' => 1095, 'total_od_borrowers' => 118,
            'fy_members_admission' => 145, 'fy_members_dropout' => 160, 'fy_disbursement_borrowers' => 220, 'fy_fully_repayment_borrowers' => 240,
            'fy_savings_collection' => 8600000, 'fy_savings_withdrawal' => 9200000, 'savings_balance' => 22100000,
            'fy_disbursement_amount' => 31800000, 'fy_loan_recovery' => 30200000, 'loan_outstanding' => 44800000,
            'recoverable' => 6500000, 'current_recovery' => 5850000, 'due_recovery' => 1420000,
            'total_od_taka' => 3650000, 'due_loanee_loan_outstanding' => 4200000,
            'own_fund_until_prior_june' => -450000, 'surplus_deficit_fy' => -180000, 'new_due' => 520000, 'due_increase_this_month' => 195000,
        ],
    ],
    [
        'id' => 4,
        'opening_date' => '2015-02-10',
        'focal_person_name' => 'MD. Alauddin',
        'kpi' => [
            'fo_count' => 6, 'total_samities' => 118, 'total_members' => 2680, 'total_borrowers' => 2310, 'total_od_borrowers' => 140,
            'fy_members_admission' => 320, 'fy_members_dropout' => 140, 'fy_disbursement_borrowers' => 480, 'fy_fully_repayment_borrowers' => 420,
            'fy_savings_collection' => 19200000, 'fy_savings_withdrawal' => 14100000, 'savings_balance' => 51200000,
            'fy_disbursement_amount' => 67500000, 'fy_loan_recovery' => 64200000, 'loan_outstanding' => 82500000,
            'recoverable' => 10500000, 'current_recovery' => 10100000, 'due_recovery' => 1180000,
            'total_od_taka' => 2890000, 'due_loanee_loan_outstanding' => 3650000,
            'own_fund_until_prior_june' => 3200000, 'surplus_deficit_fy' => 780000, 'new_due' => 240000, 'due_increase_this_month' => 60000,
        ],
    ],
    [
        'id' => 5,
        'opening_date' => '2017-09-05',
        'focal_person_name' => 'MD. Alauddin',
        'kpi' => [
            'fo_count' => 4, 'total_samities' => 91, 'total_members' => 2010, 'total_borrowers' => 1690, 'total_od_borrowers' => 88,
            'fy_members_admission' => 198, 'fy_members_dropout' => 95, 'fy_disbursement_borrowers' => 355, 'fy_fully_repayment_borrowers' => 310,
            'fy_savings_collection' => 13600000, 'fy_savings_withdrawal' => 10400000, 'savings_balance' => 36800000,
            'fy_disbursement_amount' => 49800000, 'fy_loan_recovery' => 47100000, 'loan_outstanding' => 64200000,
            'recoverable' => 8400000, 'current_recovery' => 8120000, 'due_recovery' => 890000,
            'total_od_taka' => 2150000, 'due_loanee_loan_outstanding' => 2780000,
            'own_fund_until_prior_june' => 1650000, 'surplus_deficit_fy' => 390000, 'new_due' => 175000, 'due_increase_this_month' => 25000,
        ],
    ],
    [
        'id' => 6,
        'opening_date' => '2014-06-30',
        'focal_person_name' => 'Farhana Rahman',
        'kpi' => [
            'fo_count' => 5, 'total_samities' => 110, 'total_members' => 2450, 'total_borrowers' => 2055, 'total_od_borrowers' => 130,
            'fy_members_admission' => 280, 'fy_members_dropout' => 125, 'fy_disbursement_borrowers' => 430, 'fy_fully_repayment_borrowers' => 380,
            'fy_savings_collection' => 17100000, 'fy_savings_withdrawal' => 12900000, 'savings_balance' => 45500000,
            'fy_disbursement_amount' => 59800000, 'fy_loan_recovery' => 56100000, 'loan_outstanding' => 76800000,
            'recoverable' => 9600000, 'current_recovery' => 9250000, 'due_recovery' => 1100000,
            'total_od_taka' => 3020000, 'due_loanee_loan_outstanding' => 3880000,
            'own_fund_until_prior_june' => 2780000, 'surplus_deficit_fy' => 540000, 'new_due' => 265000, 'due_increase_this_month' => 72000,
        ],
    ],
    [
        'id' => 7,
        'opening_date' => '2020-01-12',
        'focal_person_name' => 'Farhana Rahman',
        'kpi' => [
            'fo_count' => 3, 'total_samities' => 58, 'total_members' => 1180, 'total_borrowers' => 960, 'total_od_borrowers' => 55,
            'fy_members_admission' => 175, 'fy_members_dropout' => 70, 'fy_disbursement_borrowers' => 205, 'fy_fully_repayment_borrowers' => 170,
            'fy_savings_collection' => 7200000, 'fy_savings_withdrawal' => 5100000, 'savings_balance' => 18900000,
            'fy_disbursement_amount' => 27500000, 'fy_loan_recovery' => 24800000, 'loan_outstanding' => 39200000,
            'recoverable' => 5400000, 'current_recovery' => 5200000, 'due_recovery' => 640000,
            'total_od_taka' => 1520000, 'due_loanee_loan_outstanding' => 1980000,
            'own_fund_until_prior_june' => 820000, 'surplus_deficit_fy' => 210000, 'new_due' => 95000, 'due_increase_this_month' => 18000,
        ],
    ],
    [
        'id' => 8,
        'opening_date' => '2013-04-22',
        'focal_person_name' => 'Imran Chowdhury',
        'kpi' => [
            'fo_count' => 4, 'total_samities' => 97, 'total_members' => 2095, 'total_borrowers' => 1760, 'total_od_borrowers' => 102,
            'fy_members_admission' => 230, 'fy_members_dropout' => 105, 'fy_disbursement_borrowers' => 365, 'fy_fully_repayment_borrowers' => 330,
            'fy_savings_collection' => 14200000, 'fy_savings_withdrawal' => 10800000, 'savings_balance' => 39100000,
            'fy_disbursement_amount' => 51200000, 'fy_loan_recovery' => 48900000, 'loan_outstanding' => 65800000,
            'recoverable' => 8700000, 'current_recovery' => 8350000, 'due_recovery' => 920000,
            'total_od_taka' => 2380000, 'due_loanee_loan_outstanding' => 3010000,
            'own_fund_until_prior_june' => 1980000, 'surplus_deficit_fy' => 350000, 'new_due' => 210000, 'due_increase_this_month' => 45000,
        ],
    ],
    [
        'id' => 9,
        'opening_date' => '2018-08-08',
        'focal_person_name' => 'Nusrat Jahan',
        'kpi' => [
            'fo_count' => 4, 'total_samities' => 88, 'total_members' => 1925, 'total_borrowers' => 1610, 'total_od_borrowers' => 78,
            'fy_members_admission' => 205, 'fy_members_dropout' => 92, 'fy_disbursement_borrowers' => 325, 'fy_fully_repayment_borrowers' => 285,
            'fy_savings_collection' => 12900000, 'fy_savings_withdrawal' => 9900000, 'savings_balance' => 35600000,
            'fy_disbursement_amount' => 46800000, 'fy_loan_recovery' => 44100000, 'loan_outstanding' => 60100000,
            'recoverable' => 7900000, 'current_recovery' => 7680000, 'due_recovery' => 810000,
            'total_od_taka' => 2050000, 'due_loanee_loan_outstanding' => 2620000,
            'own_fund_until_prior_june' => 1520000, 'surplus_deficit_fy' => 295000, 'new_due' => 160000, 'due_increase_this_month' => 32000,
        ],
    ],
    [
        'id' => 10,
        'opening_date' => '2021-05-18',
        'focal_person_name' => 'Tanvir Ahmed',
        'kpi' => [
            'fo_count' => 2, 'total_samities' => 42, 'total_members' => 880, 'total_borrowers' => 710, 'total_od_borrowers' => 48,
            'fy_members_admission' => 130, 'fy_members_dropout' => 55, 'fy_disbursement_borrowers' => 155, 'fy_fully_repayment_borrowers' => 120,
            'fy_savings_collection' => 5400000, 'fy_savings_withdrawal' => 3800000, 'savings_balance' => 14200000,
            'fy_disbursement_amount' => 19800000, 'fy_loan_recovery' => 17600000, 'loan_outstanding' => 28500000,
            'recoverable' => 4100000, 'current_recovery' => 3920000, 'due_recovery' => 520000,
            'total_od_taka' => 1280000, 'due_loanee_loan_outstanding' => 1650000,
            'own_fund_until_prior_june' => 410000, 'surplus_deficit_fy' => 125000, 'new_due' => 88000, 'due_increase_this_month' => 15000,
        ],
    ],
];

$saved = 0;
foreach ($samples as $sample) {
    $shakha = Shakha::query()->find($sample['id']);
    if (! $shakha) {
        echo "Skip missing shakha #{$sample['id']}\n";
        continue;
    }

    $shakha->update([
        'opening_date' => $sample['opening_date'],
        'opened_at' => $sample['opening_date'],
        'focal_person_name' => $sample['focal_person_name'],
    ]);

    ShakhaAnnualKpi::query()->updateOrCreate(
        [
            'shakha_id' => $shakha->id,
            'fy_label' => $fyLabel,
        ],
        $sample['kpi']
    );

    $saved++;
    echo "OK #{$shakha->id} {$shakha->name} ({$shakha->code})\n";
}

echo "Saved {$saved} KPI rows for FY {$fyLabel}\n";
