<?php

namespace App\Http\Controllers\Back\Repas;

use App\Http\Requests\Back\Repas\CommandeRequest;
use App\Repositories\Back\Repas\CommandeRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommandeController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $commandeRepository;

    public function __construct(CommandeRepository $commandeRepository)
    {
        $this->message_create = __('La commande a été créée avec succès.');
        $this->message_update = __('La commande a été modifiée avec succès.');
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->commandeRepository = $commandeRepository;
    }

    public function getCommandes(Request $request){
        return response()->json($this->commandeRepository->getCommandes($request));
    }


    /**
     * Return ressource
     */
    public function getCommande($id, $type = 'edit')
    {
        $data = $this->commandeRepository->getCommande($id, $type);
        return response()->json(['commande' =>  $data['commande'], 'itemLists' => $data['itemLists']]);
    }


    public function getRequirements(Request $request){
        $data = $this->commandeRepository->getRequirements($request);

        return response()->json(['menus' =>  $data['menus'], 'customers' => $data['customers'], 'reference' => $data['reference']]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(CommandeRequest $request)
    {
        if($this->commandeRepository->store($request) == true){
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
    public function update(CommandeRequest $request, $id)
    {
        if($this->commandeRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function setStatutCommande(Request $request, $id)
    {
        $data = $this->commandeRepository->setStatutCommande($request, $id);

        if($data[0] == true){
            return response()->json(['erreur' => false, 'statut' => $data[1], 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->commandeRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
