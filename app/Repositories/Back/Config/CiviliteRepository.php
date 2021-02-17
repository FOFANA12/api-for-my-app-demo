<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Config;
use App\Http\Requests\Back\Config\CiviliteRequest;
use App\Models\Civilite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CiviliteRepository
{
    public function getCivilites(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('civilites')
            ->select(
                DB::raw("id, nom, statut"),
                DB::raw("IFNULL((SELECT nom FROM users WHERE civilites.created_by = users.id ),'###') as created_by"),
                DB::raw("IFNULL((SELECT nom FROM users WHERE civilites.updated_by = users.id ),'###') as updated_by")
            )
            ->where(function ($query) use($searchTerm){
                $query->whereRaw('LOWER(nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
            })
            ->whereNull('deleted_at')
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    /**
     * Return ressource
     */
    public function getCivilite($id)
    {
        return Civilite::findOrFail($id);
    }

    public function store(CiviliteRequest $request)
    {
        DB::beginTransaction();
        try {

            Civilite::create([
                'nom' => $request->input('nom'),
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

    public function update(CiviliteRequest $request, $id)
    {
            DB::beginTransaction();
            try {
                $civilite = Civilite::findOrFail($id);

                $civilite->nom = $request->input('nom');
                $civilite->statut = filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN);
                $civilite->updated_by = auth()->user()->id;
                $civilite->save();

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
            $civilites = Civilite::whereIn('id', $arrayId)->get();

            foreach ($civilites as $civilite){
                if(!$civilite->delete()){
                    $erreur = true;
                    break;
                }else{
                    $civilite->nom = $civilite->nom.$civilite->id;
                    $civilite->save();
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
