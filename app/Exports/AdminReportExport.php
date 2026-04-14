<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdminReportExport implements WithMultipleSheets
{
    public function __construct(
        protected array $globalSummary,
        protected array $eskulRanking,
        protected $schoolYear
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\GlobalSummarySheet($this->globalSummary, $this->schoolYear),
            new Sheets\EskulRankingSheet($this->eskulRanking, $this->schoolYear),
        ];
    }
}