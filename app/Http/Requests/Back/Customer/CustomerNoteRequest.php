<?php

namespace App\Http\Requests\Back\Customer;

use App\Helpers\ConfigApp;
use Illuminate\Foundation\Http\FormRequest;

class CustomerNoteRequest extends FormRequest
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
        $date_formt_php = ConfigApp::date_format_php();

        $rules = [
            'customer' => 'bail|required|exists:customers,id',
            'date' => 'bail|required|date_format:'.$date_formt_php,
            'sujet' => 'bail|nullable|max:30',
            'commentaire' => 'bail|nullable|max:100',
        ];

        return $rules;
    }

    public function attributes()
    {
        return [
            'customer' => __('client'),
            'date' => __('date'),
            'sujet' => __('sujet'),
            'commentaire' => __('commentaire'),
        ];
    }
}
