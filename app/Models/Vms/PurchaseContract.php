<?php
namespace App\Models\Vms;
use Illuminate\Database\Eloquent\Model;
class PurchaseContract extends Model
{
    protected $connection = 'mysql7';
    protected $table = 'staging_kontrak';
    public $timestamps = false;
    protected $guarded = ['*'];
}
