<?php

namespace App\Console\Commands;

use App\Models\TrLndTrainingRegistration;
use App\Services\TrainingRegistrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireWaitlistOffers extends Command
{
    protected $signature = 'training:expire-waitlist-offers';
    protected $description = 'Expire training waitlist offers past their 24h window and cascade to the next waitlisted person';

    public function handle(): void
    {
        $expired = TrLndTrainingRegistration::on('pgsql5')
            ->where('status_registration', TrLndTrainingRegistration::REG_STATUS_OFFERED)
            ->whereNotNull('process_registration_date')
            ->where('process_registration_date', '<', now()->subHours(24))
            ->get();

        foreach ($expired as $registration) {
            DB::connection('pgsql5')->beginTransaction();

            try {
                $registration->status_registration = TrLndTrainingRegistration::REG_STATUS_CANCELLED;
                $registration->process_registration_user = 'system';
                $registration->updated_by = 'system';
                $registration->updated_at = now();
                $registration->save();

                TrainingRegistrationService::cascadeToNextWaitlist($registration);

                DB::connection('pgsql5')->commit();

                Log::info('Training waitlist offer expired', ['training_regist_id' => $registration->training_regist_id]);
            } catch (\Throwable $e) {
                DB::connection('pgsql5')->rollBack();

                Log::error('Training waitlist offer expiry failed', [
                    'training_regist_id' => $registration->training_regist_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
