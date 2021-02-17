<?php

namespace App\Http\Controllers\Back\Repas;

use App\Http\Requests\Back\Repas\MenuRequest;
use App\Repositories\Back\Repas\MenuRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    private $message_create;
    private $message_update;
    private $message_delete_mass;
    private $message_error_foreign_key;
    private $message_error_inconnu;
    private $message_error;
    private $menuRepository;

    public function __construct(MenuRepository $menuRepository)
    {
        $this->message_create = __('Le menu a été créé avec succès.');
        $this->message_update = __('Le menu a été modifié avec succès.');
        $this->message_delete_mass = __("L'opération s'est terminée avec succès.");
        $this->message_error_foreign_key = __('Suppression impossible, car cette donnée est utilisée dans une autre relation.');
        $this->message_error_inconnu = __('Désolé, des erreurs se sont produites lors de la suppression.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->menuRepository = $menuRepository;
    }

    public function getMenus(Request $request){
        return response()->json($this->menuRepository->getMenus($request));
    }

    /**
     * Return ressource
     */
    public function getMenu($id, $type = 'edit')
    {
        return response()->json(['menu' => $this->menuRepository->getMenu($id, $type)]);
    }


   /* public function getRequirements(){
        return response()->json(['restaurants' =>  $this->menuRepository->getRequirements()]);
    }*/

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(MenuRequest $request)
    {
        if($this->menuRepository->store($request) == true){
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
    public function update(MenuRequest $request, $id)
    {
        if($this->menuRepository->update($request, $id) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }

    public function destroy($array)
    {
        if($this->menuRepository->destroy($array) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_delete_mass, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
