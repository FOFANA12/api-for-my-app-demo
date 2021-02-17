<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Repas;
use App\Helpers\UUIDHelpers;
use App\Http\Requests\Back\Repas\MenuRequest;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class MenuRepository
{
    public function getMenus(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');
        $restaurant = $request->get('restaurant');

        $rows = [];
        if($restaurant){
            $rows = DB::table('menus')
                ->join('restaurants','menus.restaurant','=','restaurants.id')
                ->select(
                    DB::raw("menus.id, restaurants.id as restaurant_id, menus.nom, menus.description, menus.prix, menus.image, menus.statut, restaurants.nom as restaurant")
                )
                ->where(function ($query) use($searchTerm){
                    $query->whereRaw('LOWER(restaurants.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(menus.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
                })
                ->whereNull('menus.deleted_at')
                ->whereNull('restaurants.deleted_at')
                ->where('restaurants.id','=',$restaurant)
                ->orderBy($sortColumn,$sortType)
                ->paginate($perPage);
        }


        return $rows;
    }

    /**
     * Return ressource
     */
    /*public function getRequirements()
    {
        return Restaurant::WhereNull('deleted_at')->orderBy('nom')->get(['id','nom']);
    }*/

    /**
     * Return ressource
     */
    public function getMenu($id, $type = 'edit')
    {
        if($type == 'show'){
            $menu = DB::table('menus')
                ->join('restaurants','menus.restaurant','=','restaurants.id')
                ->selectRaw('menus.id, menus.nom, restaurants.nom as restaurant, menus.description, menus.prix, menus.image, menus.statut')
                ->where('menus.id','=', $id)
                ->first();

            return $menu;
        }

        return Menu::findOrFail($id);
    }

    public function store(MenuRequest $request)
    {
        DB::beginTransaction();
        try {
            $name = null;

            $menu = Menu::create([
                'restaurant' => $request->input('restaurant'),
                'nom' => $request->input('nom'),
                'description' => $request->input('description'),
                'prix' => $request->input('prix'),
                'image' => $request->input('image'),
                'statut' => filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN),
                'created_by' =>  auth()->user()->id,
                'updated_by' =>  auth()->user()->id,
            ]);

            if($request->hasFile('image')){

                $originalImage= $request->file('image');
                $name = UUIDHelpers::getUUID().'.'.$originalImage->getClientOriginalExtension();

                Storage::disk('public')->put('uploads/'.$name, file_get_contents($originalImage));

                $menu->image = $name;
                $menu->save();
            }

            DB::commit();

            return true;
        }catch (\Exception $e) {
            DB::rollback();
        }

        return false;
    }

    public function update(MenuRequest $request, $id)
    {
            DB::beginTransaction();
            try {
                $menu = Menu::findOrFail($id);

                if($request->hasFile('image')){

                    $originalImage= $request->file('image');
                    $name = UUIDHelpers::getUUID().'.'.$originalImage->getClientOriginalExtension();

                    Storage::disk('public')->put('uploads/'.$name, file_get_contents($originalImage));

                    $menu->image = $name;
                }

                if(filter_var($request->input('clearImage'), FILTER_VALIDATE_BOOLEAN) == true){
                    $menu->image = null;
                }

                $menu->restaurant = $request->input('restaurant');
                $menu->nom = $request->input('nom');
                $menu->description = $request->input('description');
                $menu->prix = $request->input('prix');
                $menu->statut = filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN);
                $menu->updated_by = auth()->user()->id;
                $menu->save();

                DB::commit();

                return true;

            }catch (\Exception $e) {
                DB::rollback();
            }

            return false;
    }

    public function destroy($array)
    {
        $erreur = false;

        DB::beginTransaction();
        try {
            $arrayId = explode(",", $array);
            $menus = Menu::whereIn('id', $arrayId)->get();


            foreach ($menus as $menu){
                if(!$menu->delete()){
                    $erreur = true;
                    break;
                }else{
                    $menu->nom = $menu->nom.$menu->id;
                    $menu->save();
                }
            }

            if(!$erreur){
                DB::commit();
                return true;
            }else{
                DB::rollback();
            }
        }catch (\Exception $e) {

        }

        return false;
    }
}
