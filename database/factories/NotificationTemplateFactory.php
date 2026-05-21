<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['post.approved', 'post.failed', 'billing.payment_failed', 'team.invited']);

        return [
            'uuid'             => (string) Str::uuid(),
            'tenant_id'        => null,
            'template_name'    => $this->faker->words(3, true),
            'type'             => $type,
            'channel'          => $this->faker->randomElement(['email', 'push', 'slack', 'sms', 'whatsapp']),
            'subject'          => $this->faker->sentence(5),
            'message_template' => 'Hi {{user_name}}, ' . $this->faker->sentence(),
            'variables'        => ['user_name', 'workspace_name'],
            'status'           => 'active',
        ];
    }
}
