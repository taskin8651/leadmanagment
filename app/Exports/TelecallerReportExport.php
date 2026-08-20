<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TelecallerReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Date', 'Telecaller', 'Leads Assigned', 'Calls/Interactions', 'Rescheduled', 'Follow-ups Completed', 'Follow-ups Pending', 'Follow-ups Missed', 'Won', 'Lost'];
    }

    public function map($row): array
    {
        return [
            $row['date'],
            $row['telecaller'],
            $row['leads_assigned'],
            $row['interactions'],
            $row['rescheduled'],
            $row['completed'],
            $row['pending'],
            $row['missed'],
            $row['won'],
            $row['lost'],
        ];
    }
}
