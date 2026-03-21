<?php

namespace Pterodactyl\Http\Requests\Base;

use Pterodactyl\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class LocalePreferenceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'locale' => User::getRules()['language'],
        ];
    }
}
