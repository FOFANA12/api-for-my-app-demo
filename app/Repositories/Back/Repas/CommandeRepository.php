<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Repas;
use App\Helpers\ConfigApp;
use App\Http\Requests\Back\Repas\CommandeRequest;
use App\Models\CommandeRepas;
use App\Models\ItemCommandeRepas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommandeRepository
{
    public function getCommandes(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('commande_repas')
            ->join('customers','commande_repas.customer','=','customers.id')
            ->select(
                DB::raw("commande_repas.id, CONCAT(customers.nom, ' ', customers.prenom) as customer, commande_repas.date, reference, commande_repas.statut"),
                DB::raw("(SELECT COUNT(id) FROM item_commande_repas WHERE commande = commande_repas.id AND item_commande_repas.deleted_at IS NULL) as nbreMenu"),
                DB::raw("(SELECT SUM(quantite * prix) FROM item_commande_repas WHERE commande = commande_repas.id AND item_commande_repas.deleted_at IS NULL) as total")
            )
            ->where(function ($query) use($searchTerm){
                $query->whereRaw('LOWER(customers.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
            })
            ->whereNull('commande_repas.deleted_at')
            ->whereNull('customers.deleted_at')
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    /**
     * Return ressource
     */
    public function getRequirements(Request $request)
    {
        $reference = $request->get('reference');

        $menus = DB::table('menus')
                ->join('restaurants','menus.restaurant','=','restaurants.id')
                ->selectRaw('menus.id, restaurants.nom as restaurant, menus.nom, menus.prix')
                ->whereNull('menus.deleted_at')
                ->where('menus.statut','=',true)
                ->orderBy('menus.nom')
                ->get();

        $customers = DB::table('customers')
                    ->selectRaw("id, CONCAT(nom,' ', prenom) as nom")
                    ->orderBy('nom')
                    ->where('statut','=',true)
                    ->whereNull('deleted_at')
                    ->get();

        if($reference == true)
            return ['menus' => $menus, 'customers' => $customers, 'reference' => ConfigApp::getRef('commande_repas')];
        else
        return ['menus' => $menus, 'customers' => $customers, 'reference' => null];

    }

    /**
     * Return ressource
     */
    public function getCommande($id, $type = 'edit')
    {
        if($type == 'show'){
            $commande = DB::table('commande_repas')
                ->join('customers','commande_repas.customer','=','customers.id')
                ->selectRaw('commande_repas.id, customers.nom as customer, commande_repas.date, commande_repas.reference, commande_repas.statut')
                ->where('commande_repas.id','=',$id)
                ->first();

            $itemLists = DB::table('item_commande_repas')
                ->join('commande_repas','item_commande_repas.commande','=','commande_repas.id')
                ->join('menus','item_commande_repas.menu','=','menus.id')
                ->join('restaurants','menus.restaurant','=','restaurants.id')
                ->selectRaw('menus.id as menu_id, item_commande_repas.id as item_id, menus.nom as menu, restaurants.nom as restaurant, item_commande_repas.quantite, item_commande_repas.prix, (item_commande_repas.quantite * item_commande_repas.prix) as montant')
                ->where('commande_repas.id','=',$id)
                ->whereNull('item_commande_repas.deleted_at')
                ->get();

            return ['commande' => $commande, 'itemLists' => $itemLists];
        }

        $commande = CommandeRepas::findOrFail($id);
        $itemLists = DB::table('item_commande_repas')
                        ->join('commande_repas','item_commande_repas.commande','=','commande_repas.id')
                        ->join('menus','item_commande_repas.menu','=','menus.id')
                        ->join('restaurants','menus.restaurant','=','restaurants.id')
                        ->selectRaw('menus.id as menu_id, item_commande_repas.id as item_id, menus.nom as menu, restaurants.nom as restaurant, item_commande_repas.quantite, item_commande_repas.prix, (item_commande_repas.quantite * item_commande_repas.prix) as montant')
                        ->where('commande_repas.id','=',$id)
                        ->whereNull('item_commande_repas.deleted_at')
                        ->get();

        return ['commande' => $commande, 'itemLists' => $itemLists];
    }

    public function store(CommandeRequest $request)
    {
        $date_format_php = ConfigApp::date_format_php().' '.ConfigApp::heure_format_php();

        DB::beginTransaction();
        try {

            $commande = CommandeRepas::create([
                'customer' => $request->input('customer'),
                'reference' => $request->input('reference'),
                'date' => Carbon::createFromFormat($date_format_php, $request->input('date')),
                'statut' => 1,
            ]);

            if($request->input('itemLists')){
                foreach ($request->input('itemLists') as $array){
                    ItemCommandeRepas::create([
                        'commande'=>$commande->id,
                        'menu'=>$array['menu_id'],
                        'quantite'=>$array['quantite'],
                        'prix'=>$array['prix'],
                    ]);
                }
            }

            DB::commit();

            return true;
        }catch (\Exception $e) {
            Log::info($e->getMessage());
            DB::rollback();
        }

        return false;
    }

    public function update(CommandeRequest $request, $id)
    {
        $date_format_php = ConfigApp::date_format_php().' '.ConfigApp::heure_format_php();

        DB::beginTransaction();
            try {
                $commande = CommandeRepas::findOrFail($id);

                $commande->customer = $request->input('customer');
                $commande->date = Carbon::createFromFormat($date_format_php, $request->input('date'));
                $commande->save();

                $arrayId = array();
                foreach ($request->input('itemLists') as $array){
                    array_push($arrayId,$array['item_id']);
                }
                $arrayId = implode("','", $arrayId);

                ItemCommandeRepas::whereRaw("id NOT IN('$arrayId') AND commande = '$commande->id'")->delete();

                if($request->input('itemLists')) {
                    foreach ($request->input('itemLists') as $array) {

                        ItemCommandeRepas::updateOrCreate(
                            [
                                'id' => $array['item_id'],
                                'commande' => $commande->id,
                            ],
                            [
                                'menu'=>$array['menu_id'],
                                'quantite'=>$array['quantite'],
                                'prix'=>$array['prix'],
                            ]
                        );
                    }
                }

                DB::commit();

                return true;

            }catch (\Exception $e) {
                DB::rollback();
            }

            return false;
    }

    public function setStatutCommande(Request $request, $id)
    {
        DB::beginTransaction();
            try {
                $statut = $request->get('value');
                $commande = CommandeRepas::findOrFail($id);

                $commande->statut = $statut;
                $commande->save();

                DB::commit();

                return [true, $commande->statut];

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
            $commandes = CommandeRepas::whereIn('id', $arrayId)->get();


            foreach ($commandes as $commande){
                if(!$commande->delete()){
                    $erreur = true;
                    break;
                }else{
                    ItemCommandeRepas::where('commande','=',$commande->id)->delete();
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
