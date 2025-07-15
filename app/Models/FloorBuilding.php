<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloorBuilding extends Model
{
    protected $connection = 'mysql4';
    protected $table = "floorbuilding";

    protected $fillable = [
        'Building_id',
        'LokasiIDAccum',
        'Floor_name',
        'LokasiDescAccum',
        'Floor_img',
        'SVG_Image',
        'active_status',
        'Last_update_By',

    ];

    public function building()
    {
        return $this->belongsTo(CompanyBuilding::class, 'Building_id');
    }

}
