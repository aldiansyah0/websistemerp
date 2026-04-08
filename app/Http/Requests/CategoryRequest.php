<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name'));

        $this->merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->input('description') ?: null,
            'sort_order' => $this->input('sort_order') ?: 0,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('categories', 'code')->ignore($categoryId)],
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($categoryId)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
