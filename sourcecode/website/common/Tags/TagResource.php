<?php

namespace Common\Tags;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * @var Tag
     */
    public $resource;

    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'display_name' => $this->resource->display_name,
            'type' => $this->whenAppended('type'),
        ];
    }
}
