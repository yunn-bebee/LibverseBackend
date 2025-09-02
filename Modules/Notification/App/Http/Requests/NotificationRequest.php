<?php

namespace Modules\Notification\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean'
        ];
    }
}
