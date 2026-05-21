<?php

namespace App\Http\Requests\Post;

class UpdatePostRequest extends CreatePostRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        // Allow updating drafts without moving time forward
        $rules['scheduled_at'] = ['nullable', 'date'];
        return $rules;
    }
}
