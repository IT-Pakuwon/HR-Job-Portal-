<?php

namespace App\Models\Concerns;

use DateTimeInterface;

/**
 * Eloquent's default serializeDate() converts date/datetime casts to UTC ISO-8601
 * when the model is JSON-encoded (via Carbon::toJSON()). With app.timezone set to
 * Asia/Jakarta (UTC+7), that silently rolls date-only fields back a day once they
 * reach the frontend. VPL stores wall-clock values with no timezone meaning, so
 * serialize them as-is instead of converting.
 */
trait NoTimezoneShift
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->getDateFormat());
    }
}
