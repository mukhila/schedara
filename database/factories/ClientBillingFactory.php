<?php

namespace Database\Factories;

use App\Models\AgencyClient;
use App\Models\ClientBilling;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientBillingFactory extends Factory
{
    protected $model = ClientBilling::class;

    public function definition(): array
    {
        $amount = $this->faker->randomElement([9900, 24900, 49900, 99900]);
        $tax    = (int) round($amount * 0.18);

        return [
            'uuid'              => (string) Str::uuid(),
            'agency_client_id'  => AgencyClient::factory(),
            'invoice_number'    => 'INV-' . now()->format('Ym') . '-' . $this->faker->unique()->numerify('####'),
            'subscription_plan' => $this->faker->randomElement(['Starter', 'Growth', 'Agency']),
            'provider'          => 'stripe',
            'amount'            => $amount,
            'tax'               => $tax,
            'total'             => $amount + $tax,
            'currency'          => 'USD',
            'payment_status'    => $this->faker->randomElement(['open', 'paid', 'draft']),
            'due_date'          => now()->addDays(30)->toDateString(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => 'paid',
            'paid_at'        => now(),
        ]);
    }
}
