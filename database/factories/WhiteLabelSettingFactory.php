<?php

namespace Database\Factories;

use App\Models\ClientWorkspace;
use App\Models\WhiteLabelSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhiteLabelSettingFactory extends Factory
{
    protected $model = WhiteLabelSetting::class;

    public function definition(): array
    {
        return [
            'client_workspace_id' => ClientWorkspace::factory(),
            'brand_name'          => $this->faker->company(),
            'logo'                => null,
            'favicon'             => null,
            'primary_color'       => $this->faker->hexColor(),
            'secondary_color'     => $this->faker->hexColor(),
            'accent_color'        => $this->faker->hexColor(),
            'custom_domain'       => null,
            'domain_verified'     => false,
            'email_settings'      => null,
            'login_background'    => null,
            'support_email'       => $this->faker->safeEmail(),
            'support_url'         => null,
            'hide_saas_branding'  => false,
            'custom_css'          => null,
            'social_links'        => null,
        ];
    }

    public function withCustomDomain(): static
    {
        return $this->state([
            'custom_domain'   => $this->faker->domainName(),
            'domain_verified' => true,
        ]);
    }

    public function hideBranding(): static
    {
        return $this->state(['hide_saas_branding' => true]);
    }
}
