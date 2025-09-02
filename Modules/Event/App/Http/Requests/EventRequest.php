<?php

namespace Modules\Event\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_type' => 'required|string|max:100',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'location_type' => 'required|in:physical,virtual,hybrid',
            'max_attendees' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'forum_id' => 'nullable|exists:forums,id',
        ];

        // Conditionally require fields based on location_type
        $rules['physical_address'] = 'required_if:location_type,physical,hybrid|nullable|string';
        $rules['zoom_link'] = 'required_if:location_type,virtual,hybrid|nullable|url';

        return $rules;
    }

    public function messages(): array
    {
        return [
            'start_time.after' => 'The start time must be in the future.',
            'end_time.after' => 'The end time must be after the start time.',
            'physical_address.required_if' => 'Physical address is required for physical or hybrid events.',
            'zoom_link.required_if' => 'Zoom link is required for virtual or hybrid events.',
        ];
    }
}
