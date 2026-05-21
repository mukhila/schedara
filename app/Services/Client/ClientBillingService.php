<?php

namespace App\Services\Client;

use App\DTOs\Client\CreateInvoiceDTO;
use App\Events\Client\InvoiceGenerated;
use App\Events\Client\PaymentCompleted;
use App\Models\AgencyClient;
use App\Models\ClientBilling;
use App\Models\Tenant;
use App\Repositories\Contracts\ClientBillingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class ClientBillingService
{
    public function __construct(
        private readonly ClientBillingRepositoryInterface $billingRepository,
    ) {}

    public function listInvoices(AgencyClient $client, array $filters = []): LengthAwarePaginator
    {
        return $this->billingRepository->forClient($client->id, $filters);
    }

    public function createInvoice(AgencyClient $client, CreateInvoiceDTO $dto): ClientBilling
    {
        $total = $dto->amount + $dto->tax;

        $invoice = $this->billingRepository->create($client->id, [
            'subscription_plan' => $dto->subscriptionPlan,
            'provider'          => $dto->provider,
            'amount'            => $dto->amount,
            'tax'               => $dto->tax,
            'total'             => $total,
            'currency'          => $dto->currency,
            'payment_status'    => 'open',
            'due_date'          => $dto->dueDate ?? now()->addDays(30)->toDateString(),
            'line_items'        => $dto->lineItems,
            'notes'             => $dto->notes,
        ]);

        event(new InvoiceGenerated($invoice, $client));

        return $invoice;
    }

    public function markAsPaid(ClientBilling $invoice): ClientBilling
    {
        $invoice = $this->billingRepository->updateStatus($invoice, 'paid');
        event(new PaymentCompleted($invoice, $invoice->client));

        return $invoice;
    }

    public function voidInvoice(ClientBilling $invoice): ClientBilling
    {
        return $this->billingRepository->updateStatus($invoice, 'void');
    }

    public function processStripePayment(ClientBilling $invoice, string $paymentMethodId): ClientBilling
    {
        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            $intent = $stripe->paymentIntents->create([
                'amount'               => $invoice->total,
                'currency'             => strtolower($invoice->currency),
                'payment_method'       => $paymentMethodId,
                'confirm'              => true,
                'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
                'metadata'             => [
                    'invoice_uuid'   => $invoice->uuid,
                    'invoice_number' => $invoice->invoice_number,
                    'client_id'      => $invoice->agency_client_id,
                ],
            ]);

            if ($intent->status === 'succeeded') {
                $invoice->update(['provider_invoice_id' => $intent->id]);
                return $this->markAsPaid($invoice);
            }

            throw new \RuntimeException("Payment intent status: {$intent->status}");
        } catch (\Exception $e) {
            Log::error('Stripe payment failed', [
                'invoice_uuid' => $invoice->uuid,
                'error'        => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function processRazorpayPayment(ClientBilling $invoice, string $paymentId, string $orderId, string $signature): ClientBilling
    {
        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret'),
        );

        $attributes = [
            'razorpay_order_id'   => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature'  => $signature,
        ];

        $api->utility->verifyPaymentSignature($attributes);

        $invoice->update(['provider_invoice_id' => $paymentId]);

        return $this->markAsPaid($invoice);
    }

    public function agencyRevenueStats(Tenant $agency): array
    {
        $raw = $this->billingRepository->revenueForAgency($agency->id);

        return [
            'total_revenue'  => (int) ($raw->total_revenue ?? 0),
            'this_month'     => (int) ($raw->this_month ?? 0),
            'paying_clients' => (int) ($raw->paying_clients ?? 0),
        ];
    }
}
