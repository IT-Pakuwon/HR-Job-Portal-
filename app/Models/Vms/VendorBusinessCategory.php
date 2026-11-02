<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class VendorBusinessCategory extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'vendor_category';

    protected $primaryKey = 'category_id';

    public const UPDATED_AT = null;

    protected $fillable = ['category_name', 'parent_category', 'status'];

    protected $casts = ['parent_category' => 'integer', 'status' => 'integer'];
}
