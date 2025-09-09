<?php

namespace Modules\Challenge\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkUpdateChallengesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'challenge_ids' => 'required|array|min:1',
            'challenge_ids.*' => 'required|integer|exists:reading_challenges,id',
            'updates' => 'required|array',
            'updates.is_active' => 'sometimes|boolean',
            'updates.start_date' => 'sometimes|date',
            'updates.end_date' => 'sometimes|date|after_or_equal:updates.start_date',
            'updates.target_count' => 'sometimes|integer|min:1',
            'updates.badge_id' => 'sometimes|integer|exists:badges,id',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->isAdmin();
    }
}
