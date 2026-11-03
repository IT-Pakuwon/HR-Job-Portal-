<?php
namespace App\Models\Vms;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrder extends Model
{
    protected $connection = 'mysql7';
    protected $table = 'staging_po';
    public $timestamps = false;
    protected $guarded = ['*'];
}
