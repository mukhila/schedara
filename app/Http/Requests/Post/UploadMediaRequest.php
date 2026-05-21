<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file'       => ['required', 'file', 'max:102400', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
