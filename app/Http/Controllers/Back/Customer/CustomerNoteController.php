<?php

namespace App\Http\Controllers\Back\Customer;

use App\Http\Requests\Back\Customer\CustomerNoteRequest;
use App\Repositories\Back\Customer\CustomerNoteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerNoteController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $customerNoteRepository;

    public function __construct(CustomerNoteRepository $customerNoteRepository)
    {
        $this->message_create = __('La note a été créée avec succès.');
        $this->message_update = __('La note a été modifiée avec succès.');
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->customerNoteRepository = $customerNoteRepository;
    }

    public function getNotes(Request $request){
        return response()->json($this->customerNoteRepository->getNotes($request));
    }

    /**
     * Return ressource
     */
    public function getNote($id, $type = 'edit')
    {
        return response()->json(['note' =>  $this->customerNoteRepository->getNote($id, $type)]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(CustomerNoteRequest $request)
    {
        if($this->customerNoteRepository->store($request) == true){
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
    public function update(CustomerNoteRequest $request, $id)
    {
        if($this->customerNoteRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->customerNoteRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
