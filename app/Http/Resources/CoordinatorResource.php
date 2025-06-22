<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class CoordinatorResource extends JsonResource
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
            'company_id'   => $this->company_id,
            'full_name'    => $this->full_name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'nin'          => $this->nin,
            'id_card_file' => $this->id_card_file,
        ];
    }
}
