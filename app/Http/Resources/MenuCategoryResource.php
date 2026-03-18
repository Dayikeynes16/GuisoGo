<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MenuCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Virtual promotion category comes as a plain array.
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_path
                ? Storage::disk(config('filesystems.media_disk', 'public'))->url($this->image_path)
                : null,
            'sort_order' => $this->sort_order,
            'products' => MenuProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
