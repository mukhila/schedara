<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Services\Billing\RazorpayService;
use Illuminate\Console\Command;

class SyncPlansToRazorpay extends Command
{
    protected $signature   = 'billing:sync-razorpay {--plan= : Sync a single plan by slug}';
    protected $description = 'Create Razorpay plans for all active plans';

    public function handle(RazorpayService $razorpay): int
    {
        $query = Plan::active();

        if ($slug = $this->option('plan')) {
            $query->where('slug', $slug);
        }

        $plans = $query->get();

        if ($plans->isEmpty()) {
            $this->warn('No active plans found.');
            return self::FAILURE;
        }

        foreach ($plans as $plan) {
            $this->line("Syncing plan: <info>{$plan->name}</info>");

            try {
                $razorpay->syncPlan($plan);
                $this->info("  ✓ Monthly plan: {$plan->razorpay_monthly_plan_id}");
                $this->info("  ✓ Yearly plan:  {$plan->razorpay_yearly_plan_id}");
            } catch (\Throwable $e) {
                $this->error("  ✗ {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
