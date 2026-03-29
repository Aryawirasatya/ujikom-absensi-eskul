<?php
 
namespace App\Exports;

use App\Models\{Extracurricular, SchoolYear};
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EskulReportExport implements WithMultipleSheets
{
    public function __construct(
        protected array $activitySummary,
        protected array $studentSummary,
        protected Extracurricular $eskul,
        protected SchoolYear $schoolYear
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\ActivitySheet($this->activitySummary, $this->eskul, $this->schoolYear),
            new Sheets\StudentSheet($this->studentSummary, $this->eskul, $this->schoolYear),
        ];
    }
}
 