<?php

namespace App\Imports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeadsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $skipped = 0;
    public int $errored = 0;

    public function __construct(private int $clientId, private ?int $createdBy)
    {
    }

    public function collection(\Illuminate\Support\Collection $rows)
    {
        $validStatuses = ['new', 'contacted', 'qualified', 'follow-up', 'won', 'lost'];
        $validPriorities = ['low', 'medium', 'high', 'hot'];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $phone = trim((string) ($row['phone'] ?? ''));

            if ($name === '' || $phone === '') {
                $this->errored++;
                continue;
            }

            $exists = Lead::where('client_id', $this->clientId)->where('phone', $phone)->exists();
            if ($exists) {
                $this->skipped++;
                continue;
            }

            $status = strtolower(trim((string) ($row['status'] ?? 'new')));
            $priority = strtolower(trim((string) ($row['priority'] ?? 'medium')));

            Lead::create([
                'lead_number' => 'LD-' . str_pad((string) (Lead::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT),
                'name' => $name,
                'company_name' => $row['company_name'] ?? $row['company'] ?? null,
                'email' => $row['email'] ?? null,
                'phone' => $phone,
                'source' => $row['source'] ?? 'Import',
                'status' => in_array($status, $validStatuses) ? $status : 'new',
                'priority' => in_array($priority, $validPriorities) ? $priority : 'medium',
                'estimated_value' => $row['estimated_value'] ?? $row['value'] ?? null,
                'notes' => $row['notes'] ?? null,
                'client_id' => $this->clientId,
                'created_by' => $this->createdBy,
            ]);
            $this->imported++;
        }
    }
}
