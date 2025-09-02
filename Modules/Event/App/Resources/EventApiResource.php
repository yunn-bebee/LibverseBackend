<?php

namespace Modules\Event\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'event_type' => $this->event_type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'location_type' => $this->location_type,
            'physical_address' => $this->physical_address,
            'zoom_link' => $this->zoom_link,
            'max_attendees' => $this->max_attendees,
            'cover_image' => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'created_by' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'forum' => $this->forum ? [
                'id' => $this->forum->id,
                'name' => $this->forum->name,
            ] : null,
            'rsvps' => $this->rsvps->map(function ($rsvp) {
                return [
                    'user_id' => $rsvp->user_id,
                    'status' => $rsvp->status,
                ];
            }),
            'rsvp_counts' => $this->whenLoaded('rsvps', function () {
                return [
                    'going' => $this->rsvps->where('status', 'going')->count(),
                    'interested' => $this->rsvps->where('status', 'interested')->count(),
                    'not_going' => $this->rsvps->where('status', 'not_going')->count(),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
