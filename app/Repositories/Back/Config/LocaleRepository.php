<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Config;
use App\Http\Requests\Back\Config\LocaleRequest;
use App\Models\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocaleRepository
{
    public function getLocales(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('locales')
            ->select(
                DB::raw("id, code, libelle, statut"),
                DB::raw("IFNULL((SELECT nom FROM users WHERE locales.created_by = users.id ),'###') as created_by"),
                DB::raw("IFNULL((SELECT nom FROM users WHERE locales.updated_by = users.id ),'###') as updated_by")
            )
            ->where(function ($query) use($searchTerm){
                $query->whereRaw('LOWER(code) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(libelle) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
            })
            ->whereNull('deleted_at')
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    /**
     * Return ressource
     */
    public function getLocale($id)
    {
        return Locale::findOrFail($id);
    }

    public function store(LocaleRequest $request)
    {
        DB::beginTransaction();
        try {

            Locale::create([
                'code' => $request->input('code'),
                'libelle' => $request->input('libelle'),
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

    public function update(LocaleRequest $request, $id)
    {
            DB::beginTransaction();
            try {
                $locale = Locale::findOrFail($id);

                $locale->code = $request->input('code');
                $locale->libelle = $request->input('libelle');
                $locale->statut = filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN);
                $locale->updated_by = auth()->user()->id;
                $locale->save();

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
            $locales = Locale::whereIn('id', $arrayId)->get();

            foreach ($locales as $locale){
                if(!$locale->delete()){
                    $erreur = true;
                    break;
                }else{
                    $locale->code = $locale->code.$locale->id;
                    $locale->save();
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
