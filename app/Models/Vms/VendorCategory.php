<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class VendorCategory extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'vendor_category_parent';

    public const UPDATED_AT = null;

    protected $fillable = ['parent_category_name', 'ClassID', 'status'];

    protected $casts = ['status' => 'integer'];
}
