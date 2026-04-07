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
        'woid', 'wodate', 'cpny_id', 'department_id', 'wotype', 'worktypeid', 'subworktypeid', 'worequest',
        'picrequester', 'biaya_wo', 'location_id', 'sub_location_id', 'keperluan', 'budget_use', 'budget_perpost',
        'budget_cpny_id', 'budget_business_unit_id', 'budget_department_fin_id', 'budget_account_id', 'budget_activity_id',
        'budget_activity_descr', 'status', 'pic_department', 'pic_wo', 'pic_completed_wo', 'pic_wo_comment', 'flag_sppbjkt',
        'status_pekerjaan', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at',
        'completed_by', 'completed_at',
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
        return $this->belongsTo(MsLocation::class, 'location_id', 'location_id')->withDefault();
    }

    /** Sub-lokasi */
    public function sublocation()
    {
        return $this->belongsTo(MsSubLocation::class, 'sub_location_id', 'sub_location_id')->withDefault();
    }

    public function spbs()
    {
        return $this->hasMany(TrSPB::class, 'woid', 'woid');
    }

    public function sppbs()
    {
        return $this->hasMany(TrSPPB::class, 'woid', 'woid');
    }

    public function sppjs()
    {
        return $this->hasMany(TrSPPJ::class, 'woid', 'woid');
    }

    public function sppts()
    {
        return $this->hasMany(TrSPPT::class, 'woid', 'woid');
    }
}
