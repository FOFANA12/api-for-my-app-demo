<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Front\Repas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestaurantRepository
{
    public function getRestaurants(){
        $restaurants = DB::table("restaurants")
            ->selectRaw("id, nom")
            ->where("statut", true)
            ->orderBy("nom")
            ->get();

        return $restaurants;
    }

    public function getMenus(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');
        $restaurant = $request->get('selectedRestaurant');

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
                ->where('menus.statut','=',true)
                ->where('restaurants.statut','=',true)
                ->whereNull('menus.deleted_at')
                ->whereNull('restaurants.deleted_at')
                ->whereIn('restaurants.id',$restaurant)
                ->orderBy($sortColumn,$sortType)
                ->paginate($perPage);
        }else{
            $rows = DB::table('menus')
                ->join('restaurants','menus.restaurant','=','restaurants.id')
                ->select(
                    DB::raw("menus.id, restaurants.id as restaurant_id, menus.nom, menus.description, menus.prix, menus.image, menus.statut, restaurants.nom as restaurant")
                )
                ->where(function ($query) use($searchTerm){
                    $query->whereRaw('LOWER(restaurants.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(menus.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
                })
                ->where('menus.statut','=',true)
                ->where('restaurants.statut','=',true)
                ->whereNull('menus.deleted_at')
                ->whereNull('restaurants.deleted_at')
                ->orderBy($sortColumn,$sortType)
                ->paginate($perPage);
        }


        return $rows;
    }
}
