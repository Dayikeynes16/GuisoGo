<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['integer', 'between:0,6'],
            'available_from' => ['nullable', 'date_format:H:i'],
            'available_until' => ['nullable', 'date_format:H:i'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.mimes' => 'La imagen debe ser de tipo: JPG, PNG, GIF o WebP.',
            'image.max' => 'La imagen no debe pesar más de 5 MB.',
            'available_days.*.between' => 'Día de la semana inválido.',
            'available_from.date_format' => 'El formato de hora debe ser HH:MM.',
            'available_until.date_format' => 'El formato de hora debe ser HH:MM.',
        ];
    }
}
