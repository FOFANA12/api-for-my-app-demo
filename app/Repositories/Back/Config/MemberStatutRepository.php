<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Config;
use App\Http\Requests\Back\Config\MemberStatutRequest;
use App\Models\MemberStatut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberStatutRepository
{
    public function getMemberStatuts(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('member_statuts')
            ->select(
                DB::raw("id, nom, statut"),
                DB::raw("IFNULL((SELECT nom FROM users WHERE member_statuts.created_by = users.id ),'###') as created_by"),
                DB::raw("IFNULL((SELECT nom FROM users WHERE member_statuts.updated_by = users.id ),'###') as updated_by")
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
    public function getMemberStatut($id)
    {
        return MemberStatut::findOrFail($id);
    }

    public function store(MemberStatutRequest $request)
    {
        DB::beginTransaction();
        try {

            MemberStatut::create([
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

    public function update(MemberStatutRequest $request, $id)
    {
            DB::beginTransaction();
            try {
                $statut = MemberStatut::findOrFail($id);

                $statut->nom = $request->input('nom');
                $statut->statut = filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN);
                $statut->updated_by = auth()->user()->id;
                $statut->save();

                Log::info(auth()->user()->id.' User id');
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
            $statuts = MemberStatut::whereIn('id', $arrayId)->get();

            foreach ($statuts as $statut){
                if(!$statut->delete()){
                    $erreur = true;
                    break;
                }else{
                    $statut->nom = $statut->nom.$statut->id;
                    $statut->save();
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
