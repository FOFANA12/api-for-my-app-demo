<?php

namespace App\Http\Controllers\Back\Config;

use App\Http\Requests\Back\Config\RoleRequest;
use App\Repositories\Back\Config\RoleRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->message_create = __('Le droit a été créé avec succès.');
        $this->message_update = __('Le droit a été modifié avec succès.');
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->roleRepository = $roleRepository;
    }

    public function getRoles(Request $request){
        return response()->json($this->roleRepository->getRoles($request));
    }

    public function getPermissions(){
        return response()->json(['permissions' => $this->roleRepository->getPermissions()]);
    }

    /**
     * Return ressource
     */
    public function getRole($id)
    {
        $data = $this->roleRepository->getRole($id);
        return response()->json(['role' => $data['role'], 'permissions'=>$data['permissions'], 'rolePermissions'=>$data['rolePermissions']]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(RoleRequest $request)
    {
        if($this->roleRepository->store($request) == true){
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
    public function update(RoleRequest $request, $id)
    {
        if($this->roleRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->roleRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
