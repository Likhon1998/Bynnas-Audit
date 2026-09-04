<?php

namespace Tests\Feature;

use App\Services\AuditReportDocService;
use Tests\TestCase;

class AuditReportExportLayoutTest extends TestCase
{
    public function test_report_body_flows_sections_without_forced_financial_break(): void
    {
        $html = view('audits.partials.report-body', $this->minimalReportData())->render();

        $this->assertStringContainsString('class="doc-flow"', $html);
        $this->assertStringContainsString('signatures-follow', $html);
        $this->assertStringContainsString('financial-follow', $html);
        $this->assertStringContainsString('সূচিপত্র', $html);
        $this->assertStringContainsString('নিরীক্ষা কর্মকর্তার নাম', $html);
        $this->assertStringNotContainsString('doc-financial', $html);
    }

    public function test_pdf_template_breaks_only_after_cover(): void
    {
        $html = view('audits.pdf', $this->minimalReportData())->render();

        $this->assertStringContainsString('.doc-cover { page-break-after: always', $html);
        $this->assertStringNotContainsString('page-break-before: always', $html);
        $this->assertDoesNotMatchRegularExpression('/\.page\s*\{\s*page-break-after:\s*always/', $html);
        $this->assertStringNotContainsString('min-height: 297mm', $html);
    }

    public function test_doc_service_outputs_valid_docx_zip(): void
    {
        $binary = app(AuditReportDocService::class)->output($this->minimalReportData());

        $this->assertStringStartsWith('PK', $binary);
        $this->assertGreaterThan(4000, strlen($binary));
    }

    public function test_doc_service_embeds_logo_in_docx_when_path_exists(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mNk+M9Qz0AEYBxVSF+FABJAD9/qQ8WCAAAAAElFTkSuQmCC', true);
        $logoPath = tempnam(sys_get_temp_dir(), 'audit-logo-').'.png';
        file_put_contents($logoPath, $png);

        try {
            $data = $this->minimalReportData();
            $data['logoPath'] = $logoPath;

            $binary = app(AuditReportDocService::class)->output($data);
            $this->assertStringStartsWith('PK', $binary);
            $this->assertStringContainsString('word/media/', $this->extractDocxFilenames($binary));
        } finally {
            @unlink($logoPath);
        }
    }

    public function test_word_html_table_fixer_adds_borders_to_doc_tables(): void
    {
        $html = <<<'HTML'
<table class="doc-table"><tr><th>Header</th><td>Cell</td></tr></table>
<table class="header-table"><tr><td>No border</td></tr></table>
HTML;

        $fixed = app(\App\Support\WordHtmlTableFixer::class)->apply($html);

        $this->assertStringContainsString('mso-border-alt', $fixed);
        $this->assertMatchesRegularExpression('/<table[^>]*class="doc-table"[^>]*border="1"/', $fixed);
        $this->assertMatchesRegularExpression('/<table[^>]*class="header-table"[^>]*border="0"/', $fixed);
    }

    public function test_preview_uses_cover_plus_continuous_body(): void
    {
        $html = view('livewire.partials.audit-document-preview-pages', $this->minimalPreviewData())->render();

        $this->assertSame(2, substr_count($html, 'class="page"') + substr_count($html, 'class="page page-body"'));
        $this->assertStringContainsString('page-body', $html);
        $this->assertStringContainsString('doc-table', $html);
        $this->assertStringNotContainsString('class="grid ', $html);
        $this->assertStringContainsString('সূচিপত্র', $html);
        $this->assertStringContainsString('প্রতিবেদনের শ্রেণীবিন্যাস', $html);
        $this->assertStringContainsString('financial-follow', $html);
        $this->assertStringContainsString('cover-rating', view('audits.partials.cover-page', $this->minimalPreviewData() + ['logoDataUri' => null])->render());
        $this->assertStringContainsString('classification-summary', $html);
    }

    protected function extractDocxFilenames(string $binary): string
    {
        $path = tempnam(sys_get_temp_dir(), 'audit-docx-test-');
        file_put_contents($path, $binary);

        $zip = new \ZipArchive;
        $zip->open($path);
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($path);

        return implode("\n", $names);
    }

    /**
     * @return array<string, mixed>
     */
    protected function minimalPreviewData(): array
    {
        $data = $this->minimalReportData();
        $data['logoUrl'] = null;

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function minimalReportData(): array
    {
        return [
            'documentSheets' => [
                ['type' => 'cover', 'number' => 1],
                [
                    'type' => 'overview',
                    'number' => 2,
                    'rows' => [
                        [
                            'type' => 'item',
                            'serial' => '১.১',
                            'finding' => 'Test finding',
                            'amount' => '',
                            'rating' => 'Major (B)',
                            'status' => '',
                            'page_no' => '৪',
                        ],
                    ],
                ],
                ['type' => 'signatures_classification', 'number' => 3],
                ['type' => 'financial', 'number' => 4],
                ['type' => 'financial_detail', 'number' => 5],
                ['type' => 'financial_page6', 'number' => 6],
                ['type' => 'financial_page7', 'number' => 7],
                ['type' => 'financial_page8', 'number' => 8],
                ['type' => 'financial_page9', 'number' => 9],
                ['type' => 'financial_page10', 'number' => 10],
                ['type' => 'financial_page11', 'number' => 11],
                ['type' => 'financial_page12', 'number' => 12],
                ['type' => 'financial_page13', 'number' => 13],
                ['type' => 'financial_page14', 'number' => 14],
                ['type' => 'financial_page15', 'number' => 15],
                ['type' => 'financial_page16', 'number' => 16],
                ['type' => 'financial_page17', 'number' => 17],
                ['type' => 'financial_page18', 'number' => 18],
                ['type' => 'financial_page19', 'number' => 19],
                ['type' => 'financial_page20', 'number' => 20],
                ['type' => 'financial_page21', 'number' => 21],
            ],
            'logoDataUri' => null,
            'logoPath' => null,
            'ratingColor' => '#16a34a',
            'control_rating' => 'Satisfactory',
            'memo_no' => 'M-1',
            'report_date' => '2025-02-18',
            'shakha_display_name' => 'Test',
            'area_display_name' => 'Area',
            'audit_period_label' => 'Jan 2025',
            'audit_start_date' => '2025-01-01',
            'audit_end_date' => '2025-01-05',
            'working_days' => 5,
            'period_scope' => 'Full',
            'draft_sent_date' => '2025-01-06',
            'comments_received_date' => '2025-01-07',
            'auditor_name' => 'Auditor',
            'auditor_designation' => 'Officer',
            'glance_as_of' => '31 Dec 2024',
            'branch_opening_date' => '2010-01-01',
            'staff_info_as_of' => '2025-01-01',
            'glanceRows' => [
                ['left_label' => 'A', 'left_value' => '1', 'right_label' => 'B', 'right_value' => '2'],
            ],
            'staffColumns' => ['Name'],
            'staffRows' => [['cells' => ['X']]],
            'sign_auditor_name' => 'Bynnas Admin',
            'sign_auditor_designation' => 'Admin',
            'sign_auditor_date' => '2025-02-18',
            'sign_bm_name' => '',
            'sign_bm_date' => '',
            'sign_abm_name' => '',
            'sign_abm_date' => '',
            'financial_section_title' => '১.০ Accounts',
            'financialFindings' => [
                ['serial' => '১.১', 'title' => 'VAT', 'body' => 'Body', 'rating' => 'Major (B)'],
            ],
            'financial_criteria' => 'Criteria text',
            'vatObservationRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
            'taxObservationRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
            'expenseDetailRows' => [
                ['date_month' => '', 'voucher_no' => '', 'description' => 'স্টেশনারী', 'expense_amount' => '', 'vat_applicable' => '', 'vat_paid' => '', 'vat_diff' => '', 'tax_applicable' => '', 'tax_paid' => '', 'tax_diff' => '', 'is_total' => false],
            ],
            'expense_detail_risk' => 'Risk text',
            'expense_detail_root_cause' => '',
            'expense_detail_recommendation' => '',
            'expense_detail_bm_reply' => '',
            'expense_detail_responsible' => '',
            'expense_detail_resolution_date' => '',
            'finding13_serial' => '১.৩',
            'finding13_title' => 'শিরোনাম',
            'finding13_body' => 'Deposit delay',
            'finding13_amount' => '33081',
            'finding13_rating' => 'Major (B)',
            'finding13_criteria' => 'Criteria',
            'finding13_observation' => '',
            'finding13_statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
            'finding13_depositRows' => [
                ['description' => 'আয়কর (ট্যাক্স)', 'month_name' => '', 'withdrawal_date' => '', 'deposit_date' => '', 'amount' => '', 'holding_period' => ''],
            ],
            'finding13_risk' => 'Risk',
            'finding13_root_cause' => '',
            'finding13_recommendation' => '',
            'finding13_bm_reply' => '',
            'finding13_responsible' => '',
            'finding13_resolution_date' => '',
            'page6Findings' => [
                [
                    'serial' => '১.৪',
                    'title' => 'শিরোনাম',
                    'body' => 'Rent receipt missing',
                    'amount' => '',
                    'rating' => 'Major (B)',
                    'criteria' => 'Criteria 5.8',
                    'observation' => '',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হলো:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'voucherRows' => [['date' => '', 'voucher_type_no' => '', 'description' => '', 'amount' => '', 'remarks' => '']],
                    'risk' => 'Risk',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page7Findings' => [
                [
                    'serial' => '১.৬',
                    'title' => 'শিরোনাম',
                    'body' => 'Budget overspend without approval',
                    'amount' => '',
                    'rating' => 'Medium (C)',
                    'criteria' => 'Letter-39 criteria',
                    'observation' => '',
                    'detail_type' => 'budget',
                    'budget_year' => '২০২২-২০২৩',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'statsRows' => [['total_population' => '১৫', 'sample_size' => '০১৫', 'instances_found' => '০৮', 'percentage' => '৫৩%']],
                    'budgetRows' => [
                        ['budget_head' => 'ইন্টারনেট বিল', 'budget_annual' => '', 'budget_upto_june' => '', 'actual_expense' => '', 'difference' => '', 'is_total' => false],
                        ['budget_head' => 'মোট', 'budget_annual' => '', 'budget_upto_june' => '', 'actual_expense' => '', 'difference' => '', 'is_total' => true],
                    ],
                    'bonusRows' => [],
                    'risk' => 'Internal control risk',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page8Findings' => [
                [
                    'serial' => '১.৮',
                    'title' => 'শিরোনাম',
                    'body' => 'Cost of fund understatement',
                    'amount' => '২,৩৮৪',
                    'rating' => 'Minor (D)',
                    'criteria' => '10% monthly profit rule',
                    'observation' => '',
                    'detail_type' => 'cost_of_fund',
                    'detail_intro' => '',
                    'statsRows' => [['total_population' => '১২', 'sample_size' => '১২', 'instances_found' => '১০', 'percentage' => '৮৩%']],
                    'cofRows' => [
                        ['month_name' => 'জুলাই', 'opening_balance' => '', 'closing_balance' => '', 'total_balance' => '', 'avg_balance' => '', 'profit_rate_10' => '', 'monthly_profit' => '', 'branch_charged' => '', 'variance' => '', 'is_total' => false],
                        ['month_name' => 'মোট', 'opening_balance' => '', 'closing_balance' => '', 'total_balance' => '', 'avg_balance' => '', 'profit_rate_10' => '', 'monthly_profit' => '', 'branch_charged' => '', 'variance' => '', 'is_total' => true],
                    ],
                    'risk' => 'Income expense impact',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page9Findings' => [
                [
                    'serial' => '১.৯',
                    'title' => 'শিরোনাম',
                    'body' => 'Excess cash in hand',
                    'amount' => '',
                    'rating' => 'Minor (D)',
                    'criteria' => 'Cash limit criteria',
                    'observation' => '',
                    'detail_type' => 'cash',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'statsRows' => [['total_population' => '৯২', 'sample_size' => '৯২', 'instances_found' => '১৯', 'percentage' => '২১%']],
                    'cashRows' => [
                        ['date_1' => '', 'cash_1' => '', 'date_2' => '', 'cash_2' => '', 'date_3' => '', 'cash_3' => ''],
                    ],
                    'stampRows' => [],
                    'risk' => "• Risk one\n• Risk two",
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page10_section_title' => '২.০. স্থায়ী সম্পদ নিরীক্ষা (Fixed Asset Audit)',
            'page10Findings' => [
                [
                    'serial' => '২.১',
                    'title' => 'শিরোনাম',
                    'body' => 'Fixed asset not registered',
                    'amount' => '',
                    'rating' => 'Medium (C)',
                    'criteria' => 'Policy 12.1',
                    'observation' => '',
                    'detail_type' => 'asset',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'assetRows' => [
                        ['purchase_date' => '', 'voucher_no' => '', 'asset_name' => '', 'purchase_price' => '', 'previous_head' => '', 'current_location' => ''],
                    ],
                    'risk' => 'Policy risk',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page11Findings' => [
                [
                    'serial' => '২.২',
                    'title' => 'শিরোনাম',
                    'body' => 'Opening value mismatch',
                    'amount' => '',
                    'rating' => 'Medium (C)',
                    'criteria' => 'Policy 12',
                    'observation' => '',
                    'detail_type' => 'dep_compare',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'statsRows' => [['total_population' => '২৫', 'sample_size' => '২৫', 'instances_found' => '০২', 'percentage' => '০৮%']],
                    'depRows' => [
                        ['asset_group' => '', 'value_report' => '', 'value_register' => '', 'value_diff' => '', 'dep_report' => '', 'dep_register' => '', 'dep_diff' => ''],
                    ],
                    'risk' => 'Depreciation risk',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
                [
                    'serial' => '২.৩',
                    'title' => 'শিরোনাম',
                    'body' => 'Single quotation IPS purchase',
                    'amount' => '৬৬,৩৯৪',
                    'rating' => 'Minor (D)',
                    'criteria' => 'Policy 12.2.1',
                    'observation' => '',
                    'detail_type' => 'quote',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হলো:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'quoteRows' => [
                        ['product_name' => '', 'product_group' => '', 'purchase_date' => '', 'voucher_no' => '', 'amount' => '', 'quote_status' => ''],
                    ],
                    'risk' => "নিম্নমানের পণ্য সরবরাহের আশংকা।\nকম মূল্যের পণ্য অধিক মূল্যে ক্রয় আশংকা",
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page12_section_title' => '৩.০. মজুদ ব্যবস্থাপনা নিরীক্ষা (Stock management Audit)',
            'page12Findings' => [
                [
                    'serial' => '৩.১',
                    'title' => 'শিরোনাম',
                    'body' => 'Stock register not maintained',
                    'amount' => '',
                    'rating' => 'Medium (C)',
                    'criteria' => 'Policy 11.2',
                    'observation' => '',
                    'detail_type' => 'stock',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'stockRows' => [
                        ['product_name' => '', 'purchase_date_voucher' => '', 'purchase_price' => '', 'register_status' => ''],
                    ],
                    'risk' => "প্রয়োজনের অতিরিক্ত মালামাল ক্রয়ের মাধ্যমে আর্থিক ক্ষতির আশংকা\nমালামালের ব্যবহার জনিত অপচয় হওয়ার আশংকা",
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page13_section_title' => '৪.০ কার্যক্রম/পরিচালন (Operational Audit) :',
            'page13Findings' => [
                [
                    'serial' => '৪.১',
                    'title' => 'শিরোনাম',
                    'body' => 'Collected savings deposited in office fund',
                    'amount' => '২,৬৬৮',
                    'rating' => 'Unsatisfactory (F)',
                    'criteria' => 'Manual Oct-23 clause 10.2',
                    'observation' => 'Irregularities found totaling 2668 taka',
                    'detail_type' => 'samity_collection',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হলো:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'samityRows' => [
                        [
                            'samity_no' => '',
                            'member_name_id' => '',
                            'date' => '',
                            'savings' => '',
                            'voluntary' => '',
                            'term' => '',
                            'installment' => '',
                            'total_collection' => '',
                            'deposit_date' => '',
                            'deposit_amount' => '',
                            'difference' => '',
                            'staff_name_id' => '',
                        ],
                    ],
                    'risk' => 'শাখায় আর্থিক অনিয়ম সৃষ্টির আশংকা সৃষ্টি হতে পারে।',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
                [
                    'serial' => '৪.২',
                    'title' => 'শিরোনাম',
                    'body' => 'Savings collected without passbook posting',
                    'amount' => '',
                    'rating' => 'Major (B)',
                    'criteria' => '',
                    'observation' => '',
                    'detail_type' => 'none',
                    'detail_intro' => '',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'samityRows' => [],
                    'risk' => '',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page14Findings' => [
                [
                    'serial' => '৪.৩',
                    'title' => 'শিরোনাম',
                    'body' => 'Installments collected without passbook posting',
                    'amount' => '',
                    'rating' => 'Major (B)',
                    'criteria' => 'Decision 27/10/2020 memo 52',
                    'observation' => '',
                    'detail_type' => 'passbook_installment',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'passbookRows' => [
                        [
                            'samity_no' => '',
                            'member_name_id' => '',
                            'date' => '',
                            'savings_amount' => '',
                            'installment_amount' => '',
                            'savings_adjustment' => '',
                        ],
                    ],
                    'risk' => 'Financial irregularity risk',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
                [
                    'serial' => '৪.৪',
                    'title' => 'শিরোনাম',
                    'body' => 'Incorrect Sufolon loan term posting',
                    'amount' => '৫৩,৬০০',
                    'rating' => 'Medium (C)',
                    'criteria' => 'Manual section 6.9',
                    'observation' => '',
                    'detail_type' => 'sufolon_term',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'passbookRows' => [],
                    'sufolonRows' => [
                        [
                            'sl_no' => '',
                            'samity_member_id' => '',
                            'member_name' => '',
                            'disbursement_sector' => '',
                            'disbursement_date' => '',
                            'actual_term' => '',
                            'software_last_date' => '',
                            'software_term' => '',
                            'disbursed_amount' => '',
                            'excess_service_charge' => '',
                        ],
                    ],
                    'risk' => "সঠিক সময়ে আদায়যোগ্য না দেখানোর কারণে কর্মী মাঠ হতে টাকা আদায় করে আর্থিক অনিয়ম করার সুযোগ সৃষ্টি হতে পারে।\nসঠিক সময়ে আদায়যোগ্য না দেখানোর ফলে প্রকৃত বকেয়া না দেখানোর কারণে কর্মসূচির প্রকৃত চিত্র প্রদর্শিত হবে না।",
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page15Findings' => [
                [
                    'serial' => '৪.৫',
                    'title' => 'শিরোনাম',
                    'body' => 'Receivable not shown despite arrears',
                    'amount' => '৬০,০০০',
                    'rating' => 'Medium (C)',
                    'criteria' => 'Microfin360 30 day rule',
                    'observation' => '',
                    'detail_type' => 'none',
                    'detail_intro' => '',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'risk' => '',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
                [
                    'serial' => '৪.৬',
                    'title' => 'শিরোনাম',
                    'body' => 'Receivable shown 54 days after disbursement',
                    'amount' => '৪০,০০০',
                    'rating' => 'Medium (C)',
                    'criteria' => 'Microfin360 30 day rule',
                    'observation' => '',
                    'detail_type' => 'arrears_compare',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'statsRows' => [['total_population' => '৩৫', 'sample_size' => '৩৫', 'instances_found' => '১', 'percentage' => '০৩%']],
                    'arrearsRows' => [
                        [
                            'samity_no' => '',
                            'member_name_id' => '',
                            'disbursement_date' => '',
                            'loan_amount' => '',
                            'actual_due_date' => '',
                            'software_due_date' => '',
                            'installment_date' => '',
                            'actual_arrears' => '',
                            'software_arrears' => '',
                        ],
                    ],
                    'risk' => "ভুল আদায়যোগ্য সময় দেখানোর কারণে প্রকৃত বকেয়া লুকানোর সুযোগ সৃষ্টি হতে পারে।\nআর্থিক অনিয়ম ও কর্মসূচির প্রকৃত চিত্র না দেখানোর আশংকা।",
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page16Findings' => [
                [
                    'serial' => '৪.৭',
                    'title' => 'শিরোনাম',
                    'body' => 'Samity visit gaps and irregular loan disbursement',
                    'amount' => '',
                    'rating' => 'Medium (C)',
                    'criteria' => 'চিঠি-৪৮ ও চিঠি-৪৫',
                    'observation' => '',
                    'detail_type' => 'none',
                    'detail_intro' => '',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'risk' => '',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
                [
                    'serial' => '৪.৮',
                    'title' => 'শিরোনাম',
                    'body' => '28 passbooks not produced for cross-check',
                    'amount' => '২৮',
                    'rating' => 'Medium (C)',
                    'criteria' => 'চিঠি-৪৮ ও চিঠি-৪৫',
                    'observation' => '',
                    'detail_type' => 'passbook_absent',
                    'detail_intro' => '',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'passbookAbsentRows' => [
                        [
                            'staff_name' => '',
                            'samity_no' => '',
                            'total_members' => '',
                            'passbooks_received' => '',
                            'passbooks_absent' => '',
                            'officer_comment' => '',
                        ],
                    ],
                    'risk' => 'পাসবই উপস্থিত না করায় সদস্যের হিসাব যাচাই করা যায় না এবং আর্থিক অনিয়ম লুকানোর সুযোগ সৃষ্টি হতে পারে।',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page17Findings' => [
                [
                    'serial' => '৪.৯',
                    'title' => 'শিরোনাম',
                    'body' => 'Savings partial adjust installment collection',
                    'amount' => '২১,৫০০',
                    'rating' => 'Medium (C)',
                    'criteria' => '২০/০১/২০১৮ policy',
                    'observation' => '',
                    'detail_type' => 'savings_partial_adjust',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হল:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'savingsAdjustRows' => [
                        [
                            'samity_no' => '',
                            'member_name_id' => '',
                            'adjust_date' => '',
                            'adjust_amount' => '',
                        ],
                    ],
                    'risk' => 'সঞ্চয় হতে কিস্তি সমন্বয় করায় নগদ আদায় না হওয়ায় আর্থিক অনিয়মের সুযোগ সৃষ্টি হতে পারে এবং সদস্যের সঞ্চয় সুরক্ষা দুর্বল হয়।',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page18Findings' => [
                [
                    'serial' => '৪.১০',
                    'title' => 'শিরোনাম',
                    'body' => 'সদস্যদের লাভবিহীন সম্পূর্ণ সঞ্চয় ফেরত দিয়ে ড্রপআউট করা ৩৮,৯৪৪ টাকা।',
                    'amount' => '৩৮,৯৪৪',
                    'rating' => 'Medium (C)',
                    'criteria' => 'প্রতিষ্ঠান ত্যাগকারী সদস্যদের সঞ্চয় ফেরতের সময় এমআরএ (MRA) নির্দেশনা অনুযায়ী সঞ্চয়ের উপর প্রাপ্য লাভ/মুনাফা প্রদান করতে হবে।',
                    'observation' => '',
                    'detail_type' => 'dropout_savings_refund',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হল:',
                    'statsRows' => [['total_population' => '৯৩', 'sample_size' => '৩৫', 'instances_found' => '১৩', 'percentage' => '২৯%']],
                    'dropoutRefundRows' => [
                        [
                            'date' => '',
                            'samity_member_no' => '',
                            'member_name' => '',
                            'refund_amount' => '',
                        ],
                    ],
                    'risk' => 'এমআরএ নিয়ম না মানায় প্রতিষ্ঠানের সুনাম ক্ষুণ্ন হতে পারে এবং নিয়ন্ত্রক কর্তৃপক্ষ কর্তৃক ব্যবস্থা নেওয়ার আশংকা রয়েছে।',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
                [
                    'serial' => '৪.১১',
                    'title' => 'শিরোনাম',
                    'body' => 'সঞ্চয় সমন্বয় তালিকায় লেখার পরেও সদস্যের সঞ্চয় নগদে উত্তোলন করে ঋণ আদায় দেখানোর কারণে ম্যানুয়াল সঞ্চয় সমন্বয় অনুমোদনের সাথে সফটওয়্যার এর সঞ্চয় সমন্বয়ের মিল পাওয়া যায়নি ৬,১২,২৯৬ টাকা।',
                    'amount' => '৬,১২,২৯৬',
                    'rating' => 'Medium (C)',
                    'criteria' => 'মাইক্রোফিন৩৬০ সফটওয়্যারে সঞ্চয় স্থানান্তরের মাধ্যমে ঋণ সমন্বয় করতে হয়; সঞ্চয় নগদে উত্তোলন করে ঋণ আদায় দেখানো যাবে না।',
                    'observation' => '',
                    'detail_type' => 'savings_adjust_compare',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হল:',
                    'statsRows' => [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']],
                    'dropoutRefundRows' => [],
                    'savingsAdjustCompareRows' => [
                        [
                            'month_name' => '',
                            'manual_adjust' => '',
                            'software_adjust' => '',
                            'difference' => '',
                        ],
                    ],
                    'risk' => 'আর্থিক অনিয়ম সৃষ্টির আশংকা',
                    'root_cause' => '',
                    'recommendation' => '',
                    'bm_reply' => '',
                    'responsible' => '',
                    'resolution_date' => '',
                ],
            ],
            'page19_compliance_title' => '৫.০০ বিগত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদনের জবাবের কমপ্লায়েন্স (Compliance of Previous Internal Audit Report Reply)',
            'page19_compliance_period' => '',
            'page19_compliance_followup_date' => '',
            'page19ComplianceRows' => [
                [
                    'prev_para_no' => '',
                    'findings' => '',
                    'first_discovery_period' => '',
                    'management_reply' => '',
                    'current_status' => '',
                    'current_para_no' => '',
                ],
            ],
            'page20_it_title' => '৬.০০ আইটি (সফটওয়্যার) সংক্রান্ত চেকলিস্ট',
            'page20_it_org_line1' => 'ডিএসকে',
            'page20_it_org_line2' => '“অভ্যন্তরীণ নিরীক্ষা বিভাগ”',
            'page20_it_org_line3' => 'আইটি(সফটওয়্যার) বিষয়ক সংক্রান্ত',
            'page20_it_program' => 'ক্ষুদ্র ঋণ',
            'page20_it_branch' => '',
            'page20_it_instruction' => 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন',
            'page20ItChecklistRows' => [
                [
                    'sl_no' => '০১',
                    'description' => 'শাখার ল্যাপটপ গুলোতে নির্দিষ্ট সময় পর পর Anti Virus দেওয়া হয় কিনা?',
                    'compliance' => 'yes',
                    'action_owner' => '',
                    'management_comments' => '',
                    'recommendation' => '',
                ],
                [
                    'sl_no' => '০২',
                    'description' => 'Original Microsoft Window Software দিয়ে শাখার ল্যাপটপ গুলো পরিচালনা হচ্ছে কিনা?',
                    'compliance' => 'no',
                    'action_owner' => '',
                    'management_comments' => '',
                    'recommendation' => '',
                ],
            ],
            'page21_section_title' => '৭.০০ Compliance of Previous External Audit Report',
            'page21_year_of_reporting' => '2024',
            'page21_branch_name' => 'Test Branch',
            'page21ExternalAuditRows' => [
                [
                    'area_of_observation' => 'Area A',
                    'compliance_area' => 'Finance',
                    'year_of_reporting' => '2023',
                    'external_observation' => 'Observation text',
                    'compliance' => 'Complied',
                    'internal_index_no' => '1.1',
                ],
            ],
            'page21_sign_label' => 'নিরীক্ষা কর্মকর্তার স্বাক্ষরঃ',
            'page21_sign_name' => 'Auditor',
            'page21_sign_designation' => 'Officer',
        ];
    }
}
