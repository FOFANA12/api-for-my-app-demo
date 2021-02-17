<?php

namespace App\Http\Requests\Back\Event;

use App\Helpers\ConfigApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class EventRequest extends FormRequest
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

        $rules = [];

        $step = $this->step;


       /* if($this->eventFiles){
            foreach($this->eventFiles as $file) {
                dd($file['file']->getClientOriginalExtension());
            }
        }*/
        //$id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST' && !$this->id) {
            if($step == 1){
                $rules += [
                    'nom' => 'bail|required|max:50',
                    'date' => 'bail|required|date_format:'.$date_formt_php,
                    'prix' => 'bail|nullable|numeric|gte:0',
                    'max_invite' => 'bail|nullable|integer|gt:0',
                    'description' => 'bail|required',
                ];
            }else if($step == 2){
                $rules += [
                    'eventFiles' => 'bail|nullable|array',
                    'eventFiles.*.file'  => 'bail|nullable|max:3072|mimes:jpeg,png,jpg,doc,docx,pdf,csv,xls,xlsx',
                ];
            }else if($step == 3){
                $rules += [
                    'invites' => 'bail|required|array|min:1',
                    'invites.*.customer_id'  => 'bail|required|distinct|exists:customers,id',
                ];
            }else{
                $rules = [];
                if($this->input('invites')){
                    $this->merge(['invites' => json_decode($this->input('invites'),true)]);
                }
            }
        }

        if ($this->getMethod() == 'POST' && $this->id) {

            if($step == 1){
                $rules += [
                    'nom' => 'bail|required|max:50',
                    'date' => 'bail|required|date_format:'.$date_formt_php,
                    'prix' => 'bail|nullable|numeric|gte:0',
                    'max_invite' => 'bail|nullable|integer|gt:0',
                    'description' => 'bail|required',
                ];
            }else if($step == 2){

                $rules += [
                    'eventFiles' => 'bail|nullable|array',
                    'eventFiles.*.file'  => 'bail|nullable|max:3072|mimes:jpeg,png,jpg,doc,docx,pdf,csv,xls,xlsx',
                ];
            }else if($step == 3){
                $rules += [
                    'invites' => 'bail|required|array|min:1',
                    'invites.*.customer_id'  => 'bail|required|distinct|exists:customers,id',
                ];
            }else{
                $rules = [];
                if($this->input('invites')){
                    $this->merge(['invites' => json_decode($this->input('invites'),true)]);
                }
                if($this->input('eventOldFiles')){
                    $this->merge(['eventOldFiles' => json_decode($this->input('eventOldFiles'),true)]);
                }
            }
        }

        return $rules;
    }

    public function attributes()
    {

        $attributeName = array(
            'nom' => __('nom'),
            'date' => __('date'),
            'prix' => __('prix'),
            'max_invite' => __('nombre max d\'invités'),
            'description' => __('description'),
        );

        if($this->invites){

            foreach ($this->invites as $index => $array){

                $attributeName += [
                    'invites.'.$index.'.customer_id' => "identifiant invité ( ligne ".($index+1).")",
                ];
            }
        }

        if($this->eventFiles){
            foreach ($this->eventFiles as $index => $array){
                $attributeName += [
                    'eventFiles.'.$index.'.file' => "identifiant fichier ( ligne ".($index+1).")",
                ];
            }
        }

        return $attributeName;
    }
}
