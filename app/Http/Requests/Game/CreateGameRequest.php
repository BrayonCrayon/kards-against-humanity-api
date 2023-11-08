<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'expansionIds' => 'required|array',
            'expansionIds.*' => 'exists:expansions,id',
            'timer' => 'nullable|int|gte:60|lte:300',
            'hasAnimations' => 'nullable|boolean'
        ];
    }

    public function settings(): array
    {
        return [
            'selection_timer' => $this->input('timer'),
            'has_animations' => $this->input('hasAnimations', false)
        ];
    }
}
