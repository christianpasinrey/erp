<?php

declare(strict_types=1);

namespace App\Modules\Contacts\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'job_title' => $this->job_title,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'industry' => $this->industry,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'website' => $this->website,
            'notes' => $this->notes,
            'tags' => $this->tags,
            'custom_fields' => $this->custom_fields,
            'avatar_path' => $this->avatar_path,
            'logo_path' => $this->logo_path,
            'is_active' => $this->is_active,
            'source' => $this->source,
            'organization' => $this->whenLoaded('organization', fn () => [
                'id' => $this->organization->id,
                'name' => $this->organization->name,
            ]),
            'members' => $this->whenLoaded('members', fn () => $this->members->map(fn ($m) => [
                'id' => $m->id,
                'first_name' => $m->first_name,
                'last_name' => $m->last_name,
                'display_name' => $m->display_name,
                'email' => $m->email,
                'job_title' => $m->job_title,
            ])),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
