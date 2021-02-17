<?php

namespace App\Http\Controllers\Back\Config;

use App\Http\Requests\Back\Config\MemberStatutRequest;
use App\Repositories\Back\Config\MemberStatutRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MemberStatutController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $memberStatutRepository;

    public function __construct(MemberStatutRepository $memberStatutRepository)
    {
        $this->message_create = __("Le statut d'adhésion a été créé avec succès.");
        $this->message_update = __("Le statut d'adhésion a été modifié avec succès.");
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->memberStatutRepository = $memberStatutRepository;
    }

    public function getMemberStatuts(Request $request){
        return response()->json($this->memberStatutRepository->getMemberStatuts($request));
    }

    /**
     * Return ressource
     */
    public function getMemberStatut($id)
    {
        return response()->json(['statut' => $this->memberStatutRepository->getMemberStatut($id)]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(MemberStatutRequest $request)
    {
        if($this->memberStatutRepository->store($request) == true){
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
    public function update(MemberStatutRequest $request, $id)
    {
        if($this->memberStatutRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->memberStatutRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
