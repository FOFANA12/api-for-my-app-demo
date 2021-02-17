<?php

namespace App\Http\Controllers\Front\Repas;

use App\Http\Requests\Front\Repas\CommandeRequest;
use App\Repositories\Front\Repas\CommandeRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommandeController extends Controller
{
    private $message_create;
    private $message_error;
    private $message_delete_mass;
    private $commandeRepository;

    public function __construct(CommandeRepository $commandeRepository)
    {
        $this->message_create = __('La commande de référence :reference a été créée avec succès.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->commandeRepository = $commandeRepository;
    }

    public function getCommandes(Request $request){
        return response()->json($this->commandeRepository->getCommandes($request));
    }

    public function getCommande($id, $type = 'edit')
    {
        $data = $this->commandeRepository->getCommande($id, $type);
        return response()->json(['commande' =>  $data['commande'], 'itemLists' => $data['itemLists']]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(CommandeRequest $request)
    {
        $data = $this->commandeRepository->store($request);

        if($data[0] == true){
            return response()->json(['erreur' => false, 'type' => 'success', 'message' => __('La commande de référence :reference a été créée avec succès.',['reference' => $data[1]])]);
        }else{
            return response()->json(['erreur' => true, 'type' => 'error', 'message' => $this->message_error]);
        }
    }

    public function update(CommandeRequest $request, $id)
    {
        $data = $this->commandeRepository->update($request, $id);
        if($data[0] == true){
            return response()->json(['erreur' => false, 'type' => 'success', 'message' => __('La commande de référence :reference a été enregistrée avec succès.',['reference' => $data[1]])]);
        }else{
            return response()->json(['erreur' => true, 'type' => 'error', 'message' => $this->message_error]);
        }
    }

    public function destroy($array)
    {
        if($this->commandeRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'type' => 'success', 'message' => $this->message_delete_mass]);
        }else{
            return response()->json(['erreur' => true, 'type' => 'success', 'message' => $this->message_error]);
        }
    }
}
