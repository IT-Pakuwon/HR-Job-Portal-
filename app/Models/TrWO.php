<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrWO extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'tr_wo';

    protected $fillable = [
        'woid',
        'wodate',
        'cpny_id',
        'department_id',
        'wotype',
        'worktypeid',
        'subworktypeid',
        'worequest',
        'picrequester',
        'biaya_wo',
        'location_id',
        'sub_location_id',
        'keperluan',
        'status',
        'pic_department',
        'pic_wo',
        'pic_wo_done',
        'flag_sppbjkt',
        'status_wo',
        'created_by',
        'updated_by',
        'completed_by',
    ];

    /* ------------ Existing relations ------------ */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username')->withDefault();
    }

    public function picwo()
    {
        return $this->belongsTo(User::class, 'pic_wo', 'username')->withDefault();
    }

    public function picrequester()
    {
        return $this->belongsTo(User::class, 'picrequester', 'username')->withDefault();
    }

    /* ------------ New master relations ------------ */

    /** Tipe pekerjaan utama */
    public function worktype()
    {
        // foreignKey di TrWO = worktypeid, ownerKey di MsWorktype = id
        return $this->belongsTo(MsWorktype::class, 'worktypeid', 'worktypeid')->withDefault();
    }

    /** Sub-tipe pekerjaan */
    public function subworktype()
    {
        return $this->belongsTo(MsSubworktype::class, 'subworktypeid', 'subworktypeid')->withDefault();
    }

    /** Lokasi utama */
    public function location()
    {
        return $this->belongsTo(MsLocationPG::class, 'location_id', 'location_id')->withDefault();
    }

    /** Sub-lokasi */
    public function sublocation()
    {
        return $this->belongsTo(MsSubLocationPG::class, 'sub_location_id', 'sub_location_id')->withDefault();
    }
}
