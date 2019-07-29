<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

/**
 * Class Command
 * @package App\Http\Resources
 *
 * @property \App\Command $resource
 */
class Command extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'command' => $this->resource->command,
            'output' => $this->resource->output,
            'output_html' => Cache::get("cmd_output_{$this->resource->getKey()}", function () {
                return $this->resource->toAnsiHtml();
            }),
            'exit_code' => $this->resource->exit_code,
            'completed_at' => $this->resource->completed_at,
            'updated_at' => $this->resource->updated_at,
            'created_at' => $this->resource->created_at,
        ];
    }
}
