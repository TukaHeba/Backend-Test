<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->whenLoaded('product', function () {
                return new ProductResource($this->product);
            }),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->getSubtotal(),
        ];
    }
}
