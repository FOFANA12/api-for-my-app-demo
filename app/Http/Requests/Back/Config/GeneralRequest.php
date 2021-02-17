<?php

namespace App\Http\Requests\Back\Config;

use Illuminate\Foundation\Http\FormRequest;

class GeneralRequest extends FormRequest
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

        return [
            'app_name' => 'bail|nullable|string',
            'app_developer' => 'bail|nullable',
            'app_version' => 'bail|nullable',
            'app_time_zone' => 'bail|required',
            'app_currency' => 'bail|required|max:10',
            'work_time_start' => 'bail|required|integer|between:0,24',
            'work_time_end' => 'bail|required|integer|between:0,24|gt:work_time_start',
        ];
    }

    /**
     * get attribute name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'app_name' => __('nom de l\'application'),
            'app_developer' => __('editeur de l\'application'),
            'app_version' => __('version de l\'application'),
            'app_time_zone' => __('fuseau horaire'),
            'app_currency' => __('devise'),
            'work_time_start' => __('heure début'),
            'work_time_end' => __('heure fin'),
        ];
    }
}
