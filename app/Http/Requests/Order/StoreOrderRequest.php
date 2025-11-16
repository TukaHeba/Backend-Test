<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ProductQuantityAvailable;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends BaseFormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('status', 'active'),
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                new ProductQuantityAvailable($this->input('items', [])),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'items' => 'Items',
            'items.*.product_id' => 'Product ID',
            'items.*.quantity' => 'Quantity',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'array' => 'The :attribute must be an array.',
            'min' => 'The :attribute must have at least :min item(s).',
            'integer' => 'The :attribute must be an integer.',
            'exists' => 'The selected :attribute is invalid or not available.',
        ];
    }
}

