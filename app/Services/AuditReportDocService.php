<?php

namespace App\Services;

use App\Support\PhpWord\AuditReportDocxBuilder;

class AuditReportDocService
{
    public function __construct(
        private AuditReportDocxBuilder $builder,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function output(array $data): string
    {
        return $this->builder->build($data);
    }
}
