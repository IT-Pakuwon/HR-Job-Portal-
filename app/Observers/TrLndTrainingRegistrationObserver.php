<?php

namespace App\Observers;

use App\Models\TrLndTrainingRegistration;
use Illuminate\Support\Str;

class TrLndTrainingRegistrationObserver
{
    /**
     * Observer-driven unique barcode generation. Whenever a registration row
     * transitions to Approved ('C') and does not yet carry an attendance code,
     * mint one (TRN-[10 random chars]). Runs regardless of how approval was
     * triggered, so every individual row is guaranteed its own barcode.
     */
    public function updating(TrLndTrainingRegistration $registration): void
    {
        if (
            $registration->isDirty('status') &&
            $registration->status === TrLndTrainingRegistration::STATUS_APPROVED &&
            empty($registration->status_registration) &&
            empty($registration->attendance_code)
        ) {
            $registration->attendance_code = 'TRN-' . strtoupper(Str::random(10));
        }
    }
}
