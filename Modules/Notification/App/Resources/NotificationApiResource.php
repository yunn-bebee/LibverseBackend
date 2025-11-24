<?php

namespace Modules\Notification\App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationApiResource extends JsonResource
{
    public function toArray($request)
    {
      return [
        'id' => $this->id,
        'type' => $this->getNotificationType(),
        'title' => $this->data['title'] ,
        'message' => $this->data['message'] ?? '',
        'action_url' => $this->data['action_url'] ?? null,
        'action_text' => $this->data['action_text'] ?? 'View Details',
        'data' => $this->data,
        'channel' => $this->channel ?? 'database',
        'user_id' => $this->user_id,
        'is_read' => !is_null($this->read_at),
        'read_at' => $this->read_at,
        'created_at' => $this->created_at->toISOString(),
        'created_at_human' => $this->created_at->diffForHumans(),
    ];
    }

    protected function getNotificationType(): string
    {
        $type = $this->type;
        $parts = explode('\\', $type);
        return end($parts);
    }
}
