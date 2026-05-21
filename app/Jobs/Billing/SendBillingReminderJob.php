<?php

namespace App\Jobs\Billing;

use App\Mail\PaymentFailedMail;
use App\Mail\SubscriptionCreatedMail;
use App\Mail\TrialExpiredMail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBillingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    // Type: 'renewal', 'trial_expiring', 'payment_failed'
    public function __construct(
        public readonly int    $subscriptionId,
        public readonly string $type = 'renewal'
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        $subscription = Subscription::with(['tenant', 'plan'])->find($this->subscriptionId);

        if (! $subscription) {
            return;
        }

        $owner = $subscription->tenant->owner();

        if (! $owner) {
            return;
        }

        $mailable = match ($this->type) {
            'trial_expiring' => new TrialExpiredMail($subscription),
            'payment_failed' => new PaymentFailedMail($subscription),
            default          => new SubscriptionCreatedMail($subscription),
        };

        Mail::to($owner->email, $owner->name)->send($mailable);
    }
}
