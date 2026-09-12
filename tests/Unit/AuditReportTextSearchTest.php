<?php

namespace Tests\Unit;

use App\Support\AuditReportTextSearch;
use Tests\TestCase;

class AuditReportTextSearchTest extends TestCase
{
    public function test_counts_multiple_occurrences_across_locations(): void
    {
        $result = AuditReportTextSearch::search('রফিক', [
            [
                'text' => 'মাঠকর্মী রফিক এবং রফিক আলী',
                'location' => 'page4',
                'label' => 'Observation A',
                'tab' => 'page4',
                'anchor' => 'audit-block-1',
            ],
            [
                'text' => 'নাজমা ঠিক আছে',
                'location' => 'page4',
                'label' => 'Observation B',
                'tab' => 'page4',
                'anchor' => 'audit-block-2',
            ],
            [
                'text' => 'রফিক ছাড়া আর কেউ নেই',
                'location' => 'page4',
                'label' => 'Finding',
                'tab' => 'page4',
                'anchor' => 'audit-block-3',
            ],
        ]);

        $this->assertSame(3, $result['total']);
        $this->assertSame(2, $result['locations']);
        $this->assertSame(2, $result['hits'][0]['count']);
    }

    public function test_whole_word_avoids_partial_matches(): void
    {
        $partial = AuditReportTextSearch::countOccurrences('সমিতি গঠন', 'সমিতি', false);
        $whole = AuditReportTextSearch::countOccurrences('সমিতিকরণ', 'সমিতি', true);

        $this->assertSame(1, $partial);
        $this->assertSame(0, $whole);
    }
}
