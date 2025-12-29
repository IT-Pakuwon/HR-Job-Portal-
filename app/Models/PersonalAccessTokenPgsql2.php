<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken;

class PersonalAccessTokenPgsql2 extends PersonalAccessToken
{
    protected $connection = 'pgsql2';
    protected $table = 'personal_access_tokens';
}
