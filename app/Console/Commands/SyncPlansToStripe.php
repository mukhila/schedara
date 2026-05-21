<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Services\Billing\StripeService;
use Illuminate\Console\Command;

class SyncPlansToStripe extends Command
{
    protected $signature   = 'billing:sync-stripe {--plan= : Sync a single plan by slug}';
    protected $description = 'Create / update Stripe products and prices for all active plans';

    public function handle(StripeService $stripe): int
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
                $stripe->syncPlan($plan);
                $this->info("  ✓ Stripe product: {$plan->stripe_product_id}");
                $this->info("  ✓ Monthly price:  {$plan->stripe_monthly_price_id}");
                $this->info("  ✓ Yearly price:   {$plan->stripe_yearly_price_id}");
            } catch (\Throwable $e) {
                $this->error("  ✗ {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
