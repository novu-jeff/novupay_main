<?php

namespace App\Console\Commands;

use App\Models\StaritaBill;
use App\Models\StaritaHitpayTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StaritaSyncPaidStatusCommand extends Command
{
    protected $signature = 'starita:sync-paid-status {--limit=50 : Max bills to check} {--dry-run : Check only; do not update DB}';
    protected $description = 'Sync pending/initiated Starita bills with HitPay and mark paid statuses.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info('starita:sync-paid-status started.');
        Log::info('starita:sync-paid-status started', ['limit' => $limit, 'dry_run' => $dryRun]);

        $apiUrl = rtrim((string) env('HITPAY_API_URL'), '/');
        $apiKey = (string) env('HITPAY_API_KEY');
        if ($apiUrl === '' || $apiKey === '') {
            $this->error('HITPAY_API_URL or HITPAY_API_KEY is missing.');
            Log::error('starita:sync-paid-status missing hitpay config');
            return self::FAILURE;
        }

        $candidates = StaritaBill::query()
            ->whereIn('status', ['initiated', 'pending'])
            ->whereNotNull('hitpay_reference')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No eligible Starita bills found.');
            Log::info('starita:sync-paid-status no candidates');
            return self::SUCCESS;
        }

        $checked = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $now = now();

        foreach ($candidates as $bill) {
            $checked++;
            $hitpayId = (string) $bill->hitpay_reference;

            try {
                $response = Http::withHeaders([
                    'X-BUSINESS-API-KEY' => $apiKey,
                ])->get("{$apiUrl}/payment-requests/{$hitpayId}");

                if ($response->failed()) {
                    $errors++;
                    $this->warn("{$bill->reference_no}: HitPay lookup failed ({$response->status()}).");
                    Log::warning('starita:sync-paid-status hitpay lookup failed', [
                        'reference_no' => $bill->reference_no,
                        'hitpay_reference' => $hitpayId,
                        'http_status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                $remote = $response->json();
                $remoteStatus = strtolower((string) ($remote['status'] ?? ''));
                $isPaid = in_array($remoteStatus, ['completed', 'succeeded', 'paid'], true);

                if (!$isPaid) {
                    $skipped++;
                    Log::info('starita:sync-paid-status skipped', [
                        'reference_no' => $bill->reference_no,
                        'hitpay_reference' => $hitpayId,
                        'remote_status' => $remoteStatus,
                    ]);
                    continue;
                }

                if ($dryRun) {
                    $updated++;
                    $this->line("DRY-RUN: would mark paid {$bill->reference_no} ({$hitpayId}).");
                    continue;
                }

                DB::transaction(function () use ($bill, $now) {
                    $bill->update([
                        'status' => 'paid',
                        'paid_at' => $bill->paid_at ?? $now,
                    ]);

                    StaritaHitpayTransaction::where('reference_no', $bill->reference_no)
                        ->update([
                            'status' => 'paid',
                            'paid_at' => $now,
                            'updated_at' => $now,
                        ]);
                });

                $updated++;
                $this->info("Marked paid: {$bill->reference_no}");
                Log::info('starita:sync-paid-status updated', [
                    'reference_no' => $bill->reference_no,
                    'hitpay_reference' => $hitpayId,
                    'paid_at' => $now->toIso8601String(),
                ]);
            } catch (\Throwable $e) {
                $errors++;
                $this->error("{$bill->reference_no}: {$e->getMessage()}");
                Log::error('starita:sync-paid-status exception', [
                    'reference_no' => $bill->reference_no,
                    'hitpay_reference' => $hitpayId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $summary = [
            'checked' => $checked,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'dry_run' => $dryRun,
        ];

        $this->info("Finished. Checked: {$checked}, Updated: {$updated}, Skipped: {$skipped}, Errors: {$errors}");
        Log::info('starita:sync-paid-status finished', $summary);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
