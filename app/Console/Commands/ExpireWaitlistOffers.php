<?php

namespace App\Console\Commands;

use App\Models\TrTrainingRegistration;
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
        $expired = TrTrainingRegistration::on('pgsql5')
            ->where('status', TrTrainingRegistration::STATUS_OFFERED)
            ->whereNotNull('offer_expires_at')
            ->where('offer_expires_at', '<', now())
            ->get();

        foreach ($expired as $registration) {
            DB::connection('pgsql5')->beginTransaction();

            try {
                $registration->status = TrTrainingRegistration::STATUS_CANCELLED;
                $registration->updated_by = 'system';
                $registration->updated_at = now();
                $registration->save();

                TrainingRegistrationService::cascadeToNextWaitlist($registration);

                DB::connection('pgsql5')->commit();

                Log::info('Training waitlist offer expired', ['docid' => $registration->docid]);
            } catch (\Throwable $e) {
                DB::connection('pgsql5')->rollBack();

                Log::error('Training waitlist offer expiry failed', [
                    'docid' => $registration->docid,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
