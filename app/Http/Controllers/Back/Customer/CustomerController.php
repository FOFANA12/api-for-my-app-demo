<?php

namespace App\Http\Controllers\Back\Customer;

use App\Http\Requests\Back\Customer\CustomerRequest;
use App\Repositories\Back\Customer\CustomerRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->message_create = __('Le client a été créé avec succès.');
        $this->message_update = __('Le client a été modifié avec succès.');
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->customerRepository = $customerRepository;
    }

    public function getCustomers(Request $request){
        return response()->json($this->customerRepository->getCustomers($request));
    }

    /**
     * Return ressource
     */
    public function getCustomer($id, $type = 'edit')
    {
        return response()->json(['customer' =>  $this->customerRepository->getCustomer($id, $type)]);
    }


    public function getRequirements(){
        $data = $this->customerRepository->getRequirements();

        return response()->json(['locales' =>  $data['locales'], 'civilites' => $data['civilites'], 'statuts' => $data['statuts']]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(CustomerRequest $request)
    {
        if($this->customerRepository->store($request) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_create, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(CustomerRequest $request, $id)
    {
        if($this->customerRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function download($fileId, $fileName){
        if(env('APP_ENV') == 'local')
            return Storage::disk('public')->download('uploads/'.$fileId, $fileName);
        else
            return Storage::disk('s3')->download('uploads/'.$fileId, $fileName);
    }

    public function destroy($array)
    {
        if($this->customerRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
