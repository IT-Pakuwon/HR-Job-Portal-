<?php
namespace App\Models\Vms;
use Illuminate\Database\Eloquent\Model;
class ContractInvoiceRegister extends Model {
    protected $connection='mysql7';
    protected $table='ordconfirmhdrkontrak';
    public $timestamps=false;
    protected $guarded=['*'];
}
