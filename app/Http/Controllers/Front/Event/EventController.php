<?php

namespace App\Http\Controllers\Front\Event;

use App\Http\Requests\Back\Event\EventRequest;
use App\Repositories\Front\Event\EventRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    private $message_success_operation;
    private $message_error;
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->message_success_operation = __("L'opération s'est terminée avec succès.");
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->eventRepository = $eventRepository;
    }

    public function getPublished(Request $request){
        return response()->json($this->eventRepository->getPublished($request));
    }


    public function getMyEvents(Request $request)
    {
        return response()->json($this->eventRepository->getMyEvents($request));
    }

    public function getMyInvitations(Request $request)
    {
        return response()->json($this->eventRepository->getMyInvitations($request));
    }

    public function setActionEvent(Request $request)
    {
        if($this->eventRepository->setActionEvent($request) == true) {
            return response()->json(['erreur' => false, 'type' => 'success', 'message' => $this->message_success_operation]);
        }else{
            return response()->json(['erreur' => true, 'type' => 'error', 'message' => $this->message_error]);
        }
    }

    public function getEvent($id)
    {
        $data = $this->eventRepository->getEvent($id);

        return response()->json(['event' =>  $data['event']]);
    }

}
