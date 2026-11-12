<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewInventoryInquiry extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'v_inventory_inquiry';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    public static function typeLabel(?string $type): string
    {
        return [
            'RR' => 'Return Purchase Receipt',
            'PR' => 'Purchase Receipt',
            'IS' => 'Issue',
            'RI' => 'Return Issue',
        ][strtoupper(trim((string) $type))] ?? (string) $type;
    }
}
