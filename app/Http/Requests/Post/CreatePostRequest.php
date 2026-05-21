<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'content'                        => ['required', 'string', 'max:65536'],
            'caption'                         => ['nullable', 'string', 'max:2200'],
            'type'                            => ['required', 'in:text,image,video,carousel,reel,shorts'],
            'status'                          => ['required', 'in:draft,scheduled,queued'],
            'platforms'                       => ['required', 'array', 'min:1'],
            'platforms.*'                     => ['string', 'in:facebook,instagram,twitter,linkedin,pinterest,youtube,threads'],
            'platform_accounts'               => ['nullable', 'array'],
            'platform_accounts.*'             => ['nullable', 'string'],
            'scheduled_at'                    => ['nullable', 'date', 'after:now'],
            'timezone'                        => ['nullable', 'timezone'],
            'is_evergreen'                    => ['boolean'],
            'auto_repost'                     => ['boolean'],
            'repost_frequency'                => ['nullable', 'integer', 'min:1', 'max:365'],
            'hashtags'                        => ['nullable', 'array'],
            'hashtags.*'                      => ['string', 'max:100'],
            'title'                           => ['nullable', 'string', 'max:255'],
            'platform_overrides'              => ['nullable', 'array'],
            'platform_overrides.*.content'    => ['nullable', 'string'],
            'platform_overrides.*.first_comment' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required'    => 'Post content is required.',
            'platforms.required'  => 'Select at least one platform.',
            'platforms.min'       => 'Select at least one platform.',
            'scheduled_at.after'  => 'Scheduled time must be in the future.',
        ];
    }
}
