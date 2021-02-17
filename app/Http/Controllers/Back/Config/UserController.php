<?php

namespace App\Http\Controllers\Back\Config;

use App\Http\Requests\Back\Config\UserRequest;
use App\Repositories\Back\Config\UserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $message_update_profil;
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->message_create = __("L'utilisateur a été créé avec succès.");
        $this->message_update = __("L'utilisateur a été modifié avec succès.");
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_update_profil = __('Votre profil a été enregistré avec succès.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->userRepository = $userRepository;
    }

    public function getUsers(Request $request){
        return response()->json($this->userRepository->getUsers($request));
    }

    /**
     * Return ressource
     */
    public function getUser($id, $type = 'edit')
    {
        $data =  $this->userRepository->getUser($id, $type);

        return response()->json(['user' => $data['user'], 'role' => $data['role']]);
    }

    public function getRequirements(){
        $data = $this->userRepository->getRequirements();
        return response()->json(['roles' => $data['role'], 'civilites' => $data['civilite'], 'locales' => $data['locale']]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(UserRequest $request)
    {
        if($this->userRepository->store($request) == true){
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
    public function update(UserRequest $request, $id)
    {
        if($this->userRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->userRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
