<?php

namespace Modules\AdminModule\Transformers\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "account_id" => $this->account_id,
            "email" => $this->email,
            "token" => $this->token,
            'name' => $this->name ,

            
        ];
    }
}
