<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $leads)
    {
    }

    public function collection(): Collection
    {
        return $this->leads;
    }

    public function headings(): array
    {
        return ['Lead Number', 'Name', 'Company', 'Phone', 'Email', 'Source', 'Status', 'Priority', 'Client', 'Estimated Value', 'Next Follow-up', 'Created At'];
    }

    public function map($lead): array
    {
        return [
            $lead->lead_number,
            $lead->name,
            $lead->company_name,
            $lead->phone,
            $lead->email,
            $lead->source,
            ucfirst($lead->status),
            ucfirst($lead->priority),
            $lead->client->company_name ?? '',
            $lead->estimated_value,
            $lead->next_follow_up_at?->format('Y-m-d H:i'),
            $lead->created_at->format('Y-m-d H:i'),
        ];
    }
}
