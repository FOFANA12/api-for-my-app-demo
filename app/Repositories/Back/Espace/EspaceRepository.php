<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Espace;
use App\Helpers\UUIDHelpers;
use App\Http\Requests\Back\Espace\EspaceRequest;
use App\Models\Espace;
use App\Models\EspaceStatut;
use App\Models\MemberStatut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EspaceRepository
{
    public function getEspaces(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('espaces')
            ->select(
                DB::raw("espaces.id, espaces.nom, max_people, espaces.statut")
            )
            ->where(function ($query) use($searchTerm){
                $query->whereRaw('LOWER(espaces.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
            })
            ->whereNull('espaces.deleted_at')
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        $array=$rows->toArray();
        foreach ($array['data'] as $index => $row){
            $statutsArray = [];
            $status = DB::table('espace_statuts')
                ->join('member_statuts','espace_statuts.statut','=','member_statuts.id')
                ->selectRaw('member_statuts.nom')
                ->where('espace_statuts.espace','=',$row->id)
                ->whereNull('espace_statuts.deleted_at')
                ->orderBy('member_statuts.nom')
                ->get();

            foreach ($status as $statut){
                $statutsArray[] = $statut->nom;
            }

            $rows->getCollection()->transform(function($item) use($row, $statutsArray) {
                if($row->id == $item->id) $item->statuts = $statutsArray;
                return $item;
            });
        }
        return $rows;
    }

    /**
     * Return ressource
     */
    public function getRequirements()
    {
        $statuts = MemberStatut::WhereNull('deleted_at')->where('statut', true)->orderBy('nom')->get(['id','nom']);
        return $statuts;
    }

    /**
     * Return ressource
     */
    public function getEspace($id, $type = 'edit')
    {
        $status = DB::table('espace_statuts')
            ->join('member_statuts','espace_statuts.statut','=','member_statuts.id')
            ->selectRaw('member_statuts.nom, member_statuts.id')
            ->where('espace_statuts.espace','=',$id)
            ->whereNull('espace_statuts.deleted_at')
            ->orderBy('member_statuts.nom')
            ->get();

        if($type == 'show'){
            $espace = DB::table('espaces')
                ->selectRaw("espaces.id, espaces.nom, max_people, espaces.image, espaces.statut")
                ->where('espaces.id','=',$id)
                ->first();

            return ['espace' => $espace, 'statuts' => $status];;
        }

        $espace = DB::table('espaces')
            ->selectRaw("espaces.id, espaces.nom, max_people, espaces.image, espaces.statut")
            ->where('espaces.id','=',$id)
            ->first();


        return ['espace' => $espace, 'statuts' => $status];
    }


    public function store(EspaceRequest $request)
    {

        DB::beginTransaction();
        try {

            $espace = Espace::create([
                'nom' => $request->input('nom'),
                'max_people' => $request->input('max_people'),
                'statut' => filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN),
                'created_by' =>  auth()->user()->id,
                'updated_by' =>  auth()->user()->id,
            ]);

            if($request->hasFile('image')){

                $originalImage= $request->file('image');
                $name = UUIDHelpers::getUUID().'.'.$originalImage->getClientOriginalExtension();

                Storage::disk('public')->put('uploads/'.$name, file_get_contents($originalImage));

                $espace->image = $name;
                $espace->save();
            }


            foreach ($request->input('espace_statut') as $statut){
                EspaceStatut::create([
                    'espace' => $espace->id,
                    'statut' => $statut,
                ]);
            }

            DB::commit();

            return true;
        }catch (\Exception $e) {
            DB::rollback();
        }

        return false;
    }

    public function update(EspaceRequest $request, $id)
    {
            DB::beginTransaction();
            try {

                $espace = Espace::findOrFail($id);

                if($request->hasFile('image')){

                    $originalImage= $request->file('image');
                    $name = UUIDHelpers::getUUID().'.'.$originalImage->getClientOriginalExtension();

                    Storage::disk('public')->put('uploads/'.$name, file_get_contents($originalImage));

                    $espace->image = $name;
                }

                if(filter_var($request->input('clearImage'), FILTER_VALIDATE_BOOLEAN) == true){
                    $espace->image = null;
                }

                $espace->nom = $request->input('nom');
                $espace->max_people = $request->input('max_people');
                $espace->statut = filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN);
                $espace->updated_by =  auth()->user()->id;
                $espace->save();

                $arrayStatuts = $request->input('espace_statut');

                EspaceStatut::whereNotIn('statut',$arrayStatuts)->where('espace',$espace->id)->delete();

                foreach ($arrayStatuts as $statut){
                    EspaceStatut::updateOrCreate(
                        [
                            'espace' => $espace->id,
                            'statut' => $statut,
                        ]
                    );
                }

                DB::commit();

                return true;

            }catch (\Exception $e) {
                Log::info($e->getMessage());
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
            $espaces = Espace::whereIn('id', $arrayId)->get();

            foreach ($espaces as $espace){
                if(!$espace->delete()){
                    $erreur = true;
                    break;
                }else{
                    $espace->nom = $espace->nom.$espace->id;
                    $espace->save();
                    EspaceStatut::where('espace',$espace->id)->delete();
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
