<?php

namespace App\Http\Controllers\Back\Notification;

use App\Repositories\Back\Notification\NotificationRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    private $message_delete_mass;
    private $message_error;
    private $notificationRepository;


    private $message_ajout_favoris;
    private $message_recover;
    private $message_supp_favoris;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->message_ajout_favoris = __('La notification a été ajoutée aux favoris.');
        $this->message_supp_favoris = __('La notification a été supprimée des favoris.');
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_recover = __("L'opération s'est terminée avec succès.");
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->notificationRepository = $notificationRepository;
    }

    public function getNotifications(Request $request){
        return response()->json($this->notificationRepository->getNotifications($request));
    }

    public function getCounter(){
        return response()->json(['counter' => $this->notificationRepository->getCounter()]);
    }

    public function getNotification($id, $category){
        $data = $this->notificationRepository->getNotification($id, $category);

        return response()->json(['notification' => $data['notification'], 'next' => $data['next'], 'prev' => $data['prev']]);
    }

    public function getUnreadNotification(){
        return response()->json(['notifications' => $this->notificationRepository->getUnreadNotification()]);
    }

    public function readNotification($id, $category){
        return response()->json(['read_at' => $this->notificationRepository->readNotification($id, $category)]);
    }

    public function setNotificationFavoris(Request $request){
        $result = $this->notificationRepository->setNotificationFavoris($request);

        if($result['value'] == "1"){
            return response()->json(['erreur' => false, 'notifications' => $result['notifications'], 'notify' => ['title' => null, 'text' => $this->message_ajout_favoris, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => false, 'notifications' => $result['notifications'], 'notify' => ['title' => null, 'text' => $this->message_supp_favoris, 'color'=>'success']]);
        }
    }

    public function recoverInInbox(Request $request){

        if($this->notificationRepository->recoverInInbox($request) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_recover, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->notificationRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroyDefinitive($array)
    {
        if($this->notificationRepository->destroyDefinitive($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
