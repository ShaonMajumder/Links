<?php

namespace App\Http\Resources;

use App\Http\Controllers\LinkController;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        return [
            "id" => $this->id,
            "link" => $this->link,
            // "tags" => new LinkController()->getTags( $this->tags ),
            "bulkin" => $this->bulkin,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "total_open_number" => $this->total_open_number
        ];
    }
}
