<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'files'            => ['required', 'array', 'min:1', 'max:10'],
            'files.*'          => ['required', 'file', 'max:204800', 'mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,webm,mkv,pdf,doc,docx,xls,xlsx,ppt,pptx,mp3,wav'],
            'folder_id'        => ['nullable', 'string'],
            'alt_text'         => ['nullable', 'string', 'max:255'],
            'tags'             => ['nullable', 'array'],
            'tags.*'           => ['string', 'max:100'],
            'request_approval' => ['boolean'],
        ];
    }
}
