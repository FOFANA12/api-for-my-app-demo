<?php

namespace App\Http\Controllers\Back\Event;

use App\Http\Requests\Back\Event\EventRequest;
use App\Models\Event;
use App\Repositories\Back\Event\EventRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_success_operation;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->message_create = __("L'évènement a été créé avec succès.");
        $this->message_update = __("L'évènement a été modifié avec succès.");
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_success_operation = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->eventRepository = $eventRepository;
    }

    public function getEvents(Request $request){
        return response()->json($this->eventRepository->getEvents($request));
    }

    /**
     * Return ressource
     */
    public function getEvent($id, $type = 'edit')
    {
        $data = $this->eventRepository->getEvent($id, $type);

        return response()->json(['event' =>  $data['event'], 'eventFiles' =>  $data['eventFiles'], 'invites' =>  $data['invites'] ]);
    }

    public function setStatutPublication($id)
    {
        $data = $this->eventRepository->setStatutPublication($id);
        if($data[0] == true) {
            return response()->json(['erreur' => false, 'publier' => $data[1], 'notify' => ['title' => null, 'text' => $this->message_success_operation, 'color' => 'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function setActionEvent(Request $request)
    {
        $data = $this->eventRepository->setActionEvent($request);
        if($data[0] == true) {
            return response()->json(['erreur' => false, 'value' => $data[1], 'approbation_date' => $data[2], 'notify' => ['title' => null, 'text' => $this->message_success_operation, 'color' => 'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }


    public function getRequirements(){
        return response()->json(['customers' =>  $this->eventRepository->getRequirements()]);
    }

    public function checkStepper(EventRequest $request)
    {
        return response()->json(['success' =>  $this->eventRepository->checkStepper($request)]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(EventRequest $request)
    {
        if($this->eventRepository->store($request) == true){
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
    public function update(EventRequest $request, $id)
    {
        if($this->eventRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->eventRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
