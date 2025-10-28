<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrIssue extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_issue";

    protected $fillable = [
        'issueid',
        'issuedate',
        'issuetype',
        'spbid',
        'woid',
        'cpny_id',
        'department_id',
        'user_peminta',
        'issuenote',
        'budget_perpost',
        'totalissueqty',
        'totalreturnissueqty',
        'totalamountissue',
        'status',
        'created_by',
        'updated_by',
        'completed_by'
    ];
    

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function userpeminta()
    {
        return $this->belongsTo(User::class, 'user_peminta', 'username');
    }

    
}
