<?php

namespace App\Http\Controllers\Back\Espace;

use App\Helpers\ConfigApp;
use App\Http\Requests\Back\Espace\EspaceEventRequest;
use App\Repositories\Back\Espace\EspaceEventRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EspaceEventController extends Controller
{
    private $message_generique;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $espaceReservationRepository;

    public function __construct(EspaceEventRepository $espaceReservationRepository)
    {
        $this->message_generique = __("L'opération s'est terminée avec succès.");
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->espaceReservationRepository = $espaceReservationRepository;
    }

    public function getEspaceEvents(Request $request){
        return response()->json($this->espaceReservationRepository->getEspaceEvents($request));
    }

    /**
     * Return ressource
     */
    public function getSpacePlanning($id)
    {
        $data = $this->espaceReservationRepository->getSpacePlanning($id);

        return response()->json([
            'reservations' => $data['reservations'],
            'inactivePeriodes' => $data['inactivePeriodes'],
            'currentDate' => Carbon::now()->format('Y-m-d'),
            'colors' => ConfigApp::getPlanningColors()
        ]);
    }


    public function getRequirements(){
        return response()->json(['customers' => $this->espaceReservationRepository->getRequirements()]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(EspaceEventRequest $request)
    {
        if($this->espaceReservationRepository->store($request) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_generique, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }


    public function setStatutReservationSpace(Request $request, $id)
    {
        if($this->espaceReservationRepository->setStatutReservationSpace($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_generique, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }


    /*/**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    /*public function update(EspaceEventRequest $request, $id)
    {
        if($this->espaceReservationRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }*/

    public function destroy($array)
    {
        if($this->espaceReservationRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
