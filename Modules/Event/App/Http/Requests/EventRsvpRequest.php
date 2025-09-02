<?php

namespace Modules\Event\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can RSVP
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:going,interested,not_going',
        ];
    }
}
