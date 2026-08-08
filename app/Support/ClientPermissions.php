<?php

namespace App\Support;

class ClientPermissions
{
    public const ALL = [
        'create_leads' => 'Add new leads',
        'edit_leads' => 'Edit lead details & status',
        'delete_leads' => 'Delete leads',
        'complete_followups' => 'Complete follow-ups',
    ];

    public static function keys(): array
    {
        return array_keys(self::ALL);
    }
}
