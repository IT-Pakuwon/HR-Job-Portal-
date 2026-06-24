<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IFCAViewIssue extends Model
{
    protected $connection = 'sqlsrv6';
    protected $table = "ifca_view_issue";
    
}
