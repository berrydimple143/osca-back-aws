<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ReportsExport implements FromArray
{
    protected $reports;

    public function __construct(array $reports)
    {
        $this->reports = $reports;
    }

    public function array(): array
    {
        return $this->reports;
    }
}
