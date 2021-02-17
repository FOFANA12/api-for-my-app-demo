<?php

namespace App\Http\Controllers\Back\Espace;

use App\Http\Requests\Back\Espace\EspaceRequest;
use App\Repositories\Back\Espace\EspaceRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EspaceController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $espaceRepository;

    public function __construct(EspaceRepository $espaceRepository)
    {
        $this->message_create = __("L'espace a été créé avec succès.");
        $this->message_update = __("L'espace a été modifié avec succès.");
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->espaceRepository = $espaceRepository;
    }

    public function getEspaces(Request $request){
        return response()->json($this->espaceRepository->getEspaces($request));
    }

    /**
     * Return ressource
     */
    public function getEspace($id, $type = 'edit')
    {
        $data = $this->espaceRepository->getEspace($id, $type);

        return response()->json(['space' => $data['espace'], 'statuts' => $data['statuts'] ]);
    }


    public function getRequirements(){
        return response()->json(['status' =>  $this->espaceRepository->getRequirements()]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(EspaceRequest $request)
    {
        if($this->espaceRepository->store($request) == true){
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
    public function update(EspaceRequest $request, $id)
    {
        if($this->espaceRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->espaceRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
