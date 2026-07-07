<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Not backed by a physical database view — db_iamsystem (pgsql2, where tr_approval
// lives) and db_purchasing_app / db_das_test (where the document views live) are
// separate Postgres databases on the same server, so a single SQL view/join across
// them isn't possible without postgres_fdw. This model just projects the 5 columns
// of tr_approval that matter for "can this user see this document" checks; callers
// cross-reference the refnbr list against the document views in a second query.
class VUserDocument extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'tr_approval';
    public $timestamps = false;

    protected $fillable = [
        'refnbr',
        'aprv_doctype',
        'aprv_cpnyid',
        'aprv_departementid',
        'aprv_username',
    ];
}
