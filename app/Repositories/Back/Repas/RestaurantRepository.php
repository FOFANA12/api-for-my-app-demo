<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Repas;
use App\Http\Requests\Back\Repas\RestaurantRequest;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestaurantRepository
{
    public function getRestaurants(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('restaurants')
            ->select(
                DB::raw("id, nom, telephone, email, statut"),
                DB::raw("(SELECT COUNT(id) FROM menus WHERE restaurant = restaurants.id AND menus.deleted_at IS NULL) as nbre_menu")
            )
            ->where(function ($query) use($searchTerm){
                $query->whereRaw('LOWER(nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(telephone) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(email) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
            })
            ->whereNull('deleted_at')
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    /**
     * Return ressource
     */
    public function getRestaurant($id)
    {
        return Restaurant::findOrFail($id);
    }

    public function store(RestaurantRequest $request)
    {
        DB::beginTransaction();
        try {

            Restaurant::create([
                'nom' => $request->input('nom'),
                'telephone' => $request->input('telephone'),
                'email' => $request->input('email'),
                'adresse' => $request->input('adresse'),
                'statut' => filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN),
                'created_by' =>  auth()->user()->id,
                'updated_by' =>  auth()->user()->id,
            ]);

            DB::commit();

            return true;
        }catch (\Exception $e) {
            DB::rollback();
        }

        return false;
    }

    public function update(RestaurantRequest $request, $id)
    {
            DB::beginTransaction();
            try {
                $restaurant = Restaurant::findOrFail($id);

                $restaurant->nom = $request->input('nom');
                $restaurant->telephone = $request->input('telephone');
                $restaurant->email = $request->input('email');
                $restaurant->adresse = $request->input('adresse');
                $restaurant->statut = filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN);
                $restaurant->updated_by = auth()->user()->id;
                $restaurant->save();

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
            $restaurants = Restaurant::whereIn('id', $arrayId)->get();

            foreach ($restaurants as $restaurant){
                if(!$restaurant->delete()){
                    $erreur = true;
                    break;
                }else{
                    $restaurant->nom = $restaurant->nom.$restaurant->id;
                    $restaurant->telephone = $restaurant->telephone.$restaurant->id;
                    $restaurant->email = $restaurant->email.$restaurant->id;
                    $restaurant->save();
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
