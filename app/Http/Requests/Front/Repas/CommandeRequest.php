<?php

namespace App\Http\Requests\Front\Repas;

use App\Helpers\ConfigApp;
use Illuminate\Foundation\Http\FormRequest;

class CommandeRequest extends FormRequest
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

        $rules = [
            'customer' => 'bail|required|exists:customers,id',
            'date' => 'bail|required|date_format:'.$date_formt_php,
            'itemLists' => 'bail|required|array|min:1',
        ];

        $id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST') {
            $rules += [
                'itemLists.*.id'  => 'bail|required|distinct|exists:menus,id',
                'itemLists.*.quantite'  => 'bail|required|integer|gt:0',
                'itemLists.*.prix'  => 'bail|required|numeric|gt:0',
            ];
        }

        if ($this->getMethod() == 'PUT') {
            $rules += [
                'itemLists.*.menu_id'  => 'bail|required|distinct|exists:menus,id',
                'reference' => 'bail|required|max:100|unique:commande_repas,reference'.$id,
                'itemLists.*.quantite'  => 'bail|required|integer|gt:0',
                'itemLists.*.prix'  => 'bail|required|numeric|gt:0',
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
        $attributeName = array(
            'customer' => __('customer'),
            'date' => __('date'),
            'itemLists' => __('items'),
        );

        if($this->itemLists){
            foreach ($this->itemLists as $index => $array){
                $attributeName += [
                    'itemLists.'.$index.'.id' => "identifiant menu ( ligne ".($index+1).")",
                    'itemLists.'.$index.'.quantite' => "quantité ( ligne ".($index+1).")",
                    'itemLists.'.$index.'.prix' => "prix ( ligne ".($index+1).")",
                ];
            }
        }

        return $attributeName;
    }
}
