<?php

namespace App\Console\Commands;

use App\Models\KaelcoBill;
use App\Services\KaelcoWebhookNotifier;
use Illuminate\Console\Command;

class KaelcoNotifyPaidCommand extends Command
{
    protected $signature = 'kaelco:notify-paid
                            {--limit=20 : Max paid bills to notify}
                            {--reference= : Notify only this reference_no}';
    protected $description = 'Manually notify Kaelco for paid bills (backfill or retry). Normal flow is automatic via HitPay webhook.';

    public function handle(): int
    {
        $reference = $this->option('reference');
        $limit = max(1, (int) $this->option('limit'));

        if ($reference !== null && $reference !== '') {
            $bill = KaelcoBill::where('reference_no', $reference)->where('status', 'paid')->first();
            if (!$bill) {
                $this->error("No paid Kaelco bill found with reference: {$reference}");
                return self::FAILURE;
            }
            $bills = collect([$bill]);
        } else {
            $bills = KaelcoBill::where('status', 'paid')
                ->orderByDesc('paid_at')
                ->limit($limit)
                ->get();
        }

        if ($bills->isEmpty()) {
            $this->info('No paid Kaelco bills found.');
            return self::SUCCESS;
        }

        $this->info('Notifying Kaelco for ' . $bills->count() . ' paid bill(s)...');

        $ok = 0;
        $fail = 0;
        foreach ($bills as $bill) {
            $success = KaelcoWebhookNotifier::notifyPaymentCompleted($bill);
            if ($success) {
                $ok++;
                $this->line("  <info>✓</info> {$bill->reference_no}");
            } else {
                $fail++;
                $this->line("  <error>✗</error> {$bill->reference_no}");
            }
        }

        $this->newLine();
        $this->info("Done. OK: {$ok}, Failed: {$fail}");
        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
