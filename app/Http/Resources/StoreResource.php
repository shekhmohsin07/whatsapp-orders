<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo' => $this->logo_url,
            'whatsapp_number' => $this->whatsapp_number,
            'currency' => $this->currency,
            'status' => $this->status,
            'public_url' => url('/store/'.$this->slug),
            'products_count' => $this->whenCounted('products'),
            'orders_count' => $this->whenCounted('orders'),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
