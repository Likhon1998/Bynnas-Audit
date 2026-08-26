<?php

namespace App\Http\Controllers;

class AuditReportController extends Controller
{
    public function index()
    {
        return view('audits.index');
    }
}
