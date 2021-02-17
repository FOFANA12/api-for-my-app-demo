<?php

namespace App\Http\Requests\Back\Config;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class RoleRequest extends FormRequest
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
            $rules += ['role' => 'required|max:50|unique:roles,name'];
        }

        if ($this->getMethod() == 'PUT') {
            $rules += ['role' => 'required|max:50|unique:roles,name'.$id];
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
            'role' => 'nom',
        ];
    }
}
