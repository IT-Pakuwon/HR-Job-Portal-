<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaMappingDiv  extends Model
{

    protected $connection = 'pgsql4';
    protected $table = "staging_ifca_mapping_div";
    protected $primaryKey = 'id';
    protected $fillable = [
        'entity_cd', 'div_cd', 'dept_cd',
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at',
        ];
}
