<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductQuantityAvailable implements ValidationRule
{
    protected array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Extract item index from attribute
        if (! preg_match('/items\.(\d+)\.quantity/', $attribute, $matches)) {
            $fail('Invalid item index.');

            return;
        }

        $index = (int) $matches[1];
        $productId = $this->items[$index]['product_id'] ?? null;

        if (! $productId) {
            $fail('Product ID is required for this item.');

            return;
        }

        $product = Product::available()->find($productId);

        if (! $product) {
            $fail('The product is not available or out of stock.');

            return;
        }

        // Check if requested quantity is available
        if ($product->quantity < $value) {
            $fail("Insufficient quantity available for product '{$product->name}'. Only {$product->quantity} available.");
        }
    }
}
