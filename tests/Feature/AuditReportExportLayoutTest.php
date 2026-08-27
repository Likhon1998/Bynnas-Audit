<?php

namespace Tests\Feature;

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
            ],
            'logoDataUri' => null,
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
            'vatObservationRows' => [['a' => '', 'b' => '', 'c' => '', 'd' => '']],
            'taxObservationRows' => [['a' => '', 'b' => '', 'c' => '', 'd' => '']],
        ];
    }
}
