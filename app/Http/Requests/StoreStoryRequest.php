<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoryRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check(); // Hanya user yang login yang bisa "store"
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'identifier' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|url:http,https',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'body.required' => 'Konten cerita wajib diisi.',
            'identifier.required' => 'Identifier wajib diisi.',
            'category_id.exists' => 'Kategori tidak valid.',
            'images.*.url' => 'Setiap URL gambar harus berupa URL yang valid.',
        ];
    }
}

