<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// One Web Push subscription = one user on one device/browser (see database/sql/push_subscription.sql).
class SysPushSubscription extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'sys_push_subscription';

    protected $fillable = ['username', 'endpoint', 'p256dh', 'auth', 'origin', 'user_agent', 'status'];
}
