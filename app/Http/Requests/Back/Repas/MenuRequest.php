<?php

namespace App\Http\Requests\Back\Repas;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
            'restaurant' => 'bail|required|exists:restaurants,id',
            'description' => 'bail|nullable|string|max:100',
            'prix' => 'bail|required|numeric|gt:0',
            'image' => 'bail|nullable|max:3072|mimes:jpeg,png,jpg',
        ];

        $id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST' && !$this->id) {
            $rules += [
                'nom' => 'bail|required|string|max:20|unique:menus,nom',
            ];
        }

        if ($this->getMethod() == 'POST' && $this->id) {
            $rules += [
                'nom' => 'bail|required|string|max:20|unique:menus,nom'.$id,
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
            'restaurant' => __('restaurant'),
            'nom' => __('nom'),
            'description' => __('description'),
            'prix' => __('prix'),
            'image' => __('image'),
        ];
    }
}
