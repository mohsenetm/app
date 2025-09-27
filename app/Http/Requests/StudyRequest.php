<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $card_id
 * @property string $action
 */
class StudyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'card_id' => 'sometimes',
            'action' => 'required|string|in:initial,again,hard,good,easy',
        ];
    }
}
