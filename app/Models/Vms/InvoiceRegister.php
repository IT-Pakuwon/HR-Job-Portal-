<?php
namespace App\Models\Vms;
use Illuminate\Database\Eloquent\Model;
class InvoiceRegister extends Model {
    protected $connection='mysql7';
    protected $table='ordconfirmhdr';
    public $timestamps=false;
    protected $guarded=['*'];
}
