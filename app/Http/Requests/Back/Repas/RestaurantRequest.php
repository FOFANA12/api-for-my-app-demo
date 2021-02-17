<?php

namespace App\Http\Requests\Back\Repas;

use Illuminate\Foundation\Http\FormRequest;

class RestaurantRequest extends FormRequest
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
            'adresse' => 'bail|nullable|string|max:100',
        ];

        $id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST') {
            $rules += [
                'nom' => 'bail|required|string|max:20|unique:restaurants,nom',
                'email' => 'bail|nullable|string|email|max:50|unique:restaurants,email',
                'telephone' => 'bail|nullable|string|max:15|unique:restaurants,telephone',
            ];
        }

        if ($this->getMethod() == 'PUT') {
            $rules += [
                'nom' => 'bail|required|string|max:20|unique:restaurants,nom'.$id,
                'email' => 'bail|nullable|string|email|max:50|unique:restaurants,email'.$id,
                'telephone' => 'bail|nullable|string|max:15|unique:restaurants,telephone'.$id,
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
            'email' => __('email'),
            'telephone' => __('téléphone'),
            'adresse' => __('adresse'),
        ];
    }
}
