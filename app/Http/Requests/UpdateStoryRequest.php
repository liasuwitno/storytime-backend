<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoryRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check(); // Hanya user yang login yang bisa "update"
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'required|url',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'body.required' => 'Konten cerita wajib diisi.',
            'category_id.exists' => 'Kategori tidak valid.',
            'images.required' => 'Gambar wajib diunggah.',
            'images.*.url' => 'Setiap URL gambar harus berupa URL yang valid.',
        ];
    }
}
