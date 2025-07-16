<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetsLocation extends Model
{
    protected $connection = 'mysql4';
    protected $table = "asset_location";

    protected $fillable = [
        'floor_id',
        'location_name',
        'location_code',
        'location_position',
        'location_img',
        'location_tumbnail',
        'position_x',
        'position_y',
        'svg_width',
        'svg_height',
        'active_status',
        'Last_update_By',
    ];

    public function floor()
    {
        return $this->belongsTo(FloorBuilding::class, 'floor_id');
    }

}
