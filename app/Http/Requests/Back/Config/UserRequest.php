<?php

namespace App\Http\Requests\Back\Config;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'nom' => 'bail|required|string|max:50',
            'role' => 'bail|required|exists:roles,id',
            'civilite' => 'bail|required|exists:civilites,id',
            'locale' => 'bail|required|exists:locales,id',
        ];

        $id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST') {
            $rules += [
                'email' => 'bail|required|string|email|max:50|unique:users',
                'telephone' => 'bail|nullable|string|max:15|unique:users',
            ];
        }

        if ($this->getMethod() == 'PUT') {
            $rules += [
                'email' => 'bail|required|string|email|max:50|unique:users,email'.$id,
                'telephone' => 'bail|nullable|string|max:15|unique:users,telephone'.$id,
                'password' => 'bail|nullable|string|min:8|confirmed',
            ];
        }

        return $rules;
    }

    /**
     * get attribute name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'nom' => __('nom utilisateur'),
            'email' => __('email'),
            'telephone' => __('téléphone'),
            'role' => __('droit'),
            'password' => __('mot de passe'),
            'civilite' => __('civilité'),
            'locale' => __('langue'),
        ];
    }
}
