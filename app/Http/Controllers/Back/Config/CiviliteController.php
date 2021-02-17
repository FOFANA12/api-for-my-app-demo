<?php

namespace App\Http\Controllers\Back\Config;

use App\Http\Requests\Back\Config\CiviliteRequest;
use App\Repositories\Back\Config\CiviliteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CiviliteController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $civiliteRepository;

    public function __construct(CiviliteRepository $civiliteRepository)
    {
        $this->message_create = __('La civilité a été créée avec succès.');
        $this->message_update = __('La civilité a été modifiée avec succès.');
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->civiliteRepository = $civiliteRepository;
    }

    public function getCivilites(Request $request){
        return response()->json($this->civiliteRepository->getCivilites($request));
    }

    /**
     * Return ressource
     */
    public function getCivilite($id)
    {
        return response()->json(['civilite' => $this->civiliteRepository->getCivilite($id)]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(CiviliteRequest $request)
    {
        if($this->civiliteRepository->store($request) == true){
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
    public function update(CiviliteRequest $request, $id)
    {
        if($this->civiliteRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->civiliteRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
