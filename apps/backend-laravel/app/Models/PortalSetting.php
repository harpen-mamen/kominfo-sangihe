<?php

namespace App\Models;

class PortalSetting extends ModelIndonesia
{
    protected $table = 'portal_settings';

    protected function casts(): array
    {
        return [
            'key' => 'string',
            'value' => 'string',
            'type' => 'string',
        ];
    }
}

