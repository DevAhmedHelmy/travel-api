<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToursListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date_form' => 'date',
            'date_to' => 'date',
            'price_from' => 'numeric',
            'price_to' => 'numeric',
            'sort_by' => Rule::in(['price']),
            'sort_order' => Rule::in(['asc', 'desc']),
        ];
    }

    public function messages()
    {
        return [
            'sort_by' => "The 'sort by' parameter accepted only 'price' value.",
            'sort_order' => "The 'sort order' parameter accepted only 'asc' or 'desc' value.",
        ];
    }
}
