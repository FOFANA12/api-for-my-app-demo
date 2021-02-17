<?php

namespace App\Http\Requests\Back\Espace;

use Illuminate\Foundation\Http\FormRequest;

class EspaceRequest extends FormRequest
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
            'image' => 'bail|nullable|max:3072|mimes:jpeg,png,jpg',
            'max_people' => 'bail|required|integer|gt:0',
            'espace_statut' => 'bail|required|array|min:1',
            'espace_statut.*' => 'bail|required|exists:member_statuts,id',
        ];

        $id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST' && !$this->id) {
            $rules += [
                'nom' => 'bail|required|string|max:50|unique:espaces,nom',
            ];
        }

        if ($this->getMethod() == 'POST' && $this->id) {
            $rules += [
                'nom' => 'bail|required|string|max:50|unique:espaces,nom'.$id,
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
        $attributeName = [
            'nom' => __('nom'),
            'image' => __('image'),
            'max_people' => __('nombre max de personne'),
            'espace_statut' => __('statuts espace'),
        ];

        return $attributeName;
    }
}
