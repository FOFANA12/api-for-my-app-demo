<?php

namespace App\Http\Requests\Back\Config;

use Illuminate\Foundation\Http\FormRequest;

class LocaleRequest extends FormRequest
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
            'libelle' => 'required|max:15',
        ];

        $id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST') {
            $rules += [
                'code' => 'required|max:10|unique:locales,code'
            ];
        }

        if ($this->getMethod() == 'PUT') {
            $rules += [
                'code' => 'required|max:10|unique:locales,code'.$id
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
            'code' => __('code'),
            'libelle' => __('Libellé'),
        ];
    }
}
