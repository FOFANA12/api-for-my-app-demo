<?php

namespace App\Http\Requests\Back\Espace;

use App\Helpers\ConfigApp;
use Illuminate\Foundation\Http\FormRequest;

class EspaceEventRequest extends FormRequest
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
        $date_formt_php = ConfigApp::date_format_php().' '.ConfigApp::heure_format_php();

        $store_type = $this->store_type ? $this->store_type : '';

        $rules = [];

        if($store_type == 'reservation'){
            $rules += [
                'customer' => 'bail|required|exists:customers,id',
                'espace' => 'bail|required|exists:espaces,id',
                'start_date' => 'bail|required|date_format:'.$date_formt_php,
                'end_date' => 'bail|required|date_format:'.$date_formt_php.'|after:start_date',
            ];
        }else{
            $rules += [
                'description' => 'bail|nullable|max:100',
                'start_date' => 'bail|required|date_format:'.$date_formt_php,
                'end_date' => 'bail|required|date_format:'.$date_formt_php.'|after:start_date',
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
            'customer' => __('client'),
            'espace' => __('espace'),
            'description' => __('description'),
            'start_date' => __('date début'),
            'end_date' => __('date fin'),
        ];
    }
}
