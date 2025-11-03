<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('create_courses');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['required', 'string', 'min:50'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'difficulty_level' => ['required', 'in:beginner,intermediate,advanced'],
            'estimated_duration' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'category' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
            'requirements' => ['nullable', 'array', 'max:20'],
            'requirements.*' => ['string', 'max:255'],
            'what_you_learn' => ['nullable', 'array', 'max:20'],
            'what_you_learn.*' => ['string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Course title is required.',
            'title.min' => 'Course title must be at least 3 characters.',
            'description.required' => 'Course description is required.',
            'description.min' => 'Course description must be at least 50 characters.',
            'difficulty_level.required' => 'Please select a difficulty level.',
            'price.required' => 'Course price is required.',
            'featured_image.image' => 'Featured image must be a valid image file.',
            'featured_image.max' => 'Featured image must not exceed 2MB.',
        ];
    }
}
