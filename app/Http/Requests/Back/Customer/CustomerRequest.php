<?php

namespace App\Http\Requests\Back\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
            'nom' => 'bail|required|string|max:50',
            'prenom' => 'bail|required|string|max:50',
            'entreprise' => 'bail|nullable|max:30',
            'adresse' => 'bail|nullable|max:100',
            'member_statut' => 'bail|required|exists:member_statuts,id',
            'num_passeport' => 'bail|nullable|max:15',
            'passeport_file' => 'bail|nullable|file|max:3072|mimes:jpeg,png,jpg,pdf',
            'condition_medical' => 'bail|nullable|max:50',
            'contact_urgence_nom' => 'bail|nullable|max:50',
            'contact_urgence_telephone' => 'bail|nullable|max:15',
            'civilite' => 'bail|required|exists:civilites,id',
            'locale' => 'bail|required|exists:locales,id',
        ];

        $id = $this->id ? ','.$this->id : '';

        if ($this->getMethod() == 'POST' && !$this->id) {
            $rules += [
                'email' => 'bail|required|string|email|max:50|unique:customers',
                'telephone' => 'bail|nullable|string|max:15|unique:customers',
            ];
        }

        if ($this->getMethod() == 'POST' && $this->id) {
            $rules += [
                'email' => 'bail|required|string|email|max:50|unique:customers,email'.$id,
                'telephone' => 'bail|nullable|string|max:15|unique:customers,telephone'.$id,
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'nom' => __('nom'),
            'prenom' => __('prenom'),
            'entreprise' => __('entreprise'),
            'adresse' => __('adresse'),
            'member_statut' => __('statut d\'adhésion'),
            'email' => __('email'),
            'telephone' => __('téléphone'),
            'num_passeport' => __('n° passeport'),
            'condition_medical' => __('condition médicale'),
            'contact_urgence_nom' => __('nom contact d\'urgence'),
            'contact_urgence_telephone' => __('téléphone contact d\'urgence'),
            'civilite' => __('civilité'),
            'locale' => __('langue'),
            'passeport_file' => __('fichier passeport'),
        ];
    }
}
