<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RedirectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'status' => $this->status,
            'url' => $this->url,
            'last_access' => $this->last_access,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
