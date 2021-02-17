<?php

namespace App\Http\Requests\Back\Config;

use Illuminate\Foundation\Http\FormRequest;

class MemberStatutRequest extends FormRequest
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
        $rules = [];

        $id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST') {
            $rules += [
                'nom' => 'required|max:15|unique:member_statuts,nom'
            ];
        }

        if ($this->getMethod() == 'PUT') {
            $rules += [
                'nom' => 'required|max:15|unique:member_statuts,nom'.$id
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
            'nom' => __('nom'),
        ];
    }
}
