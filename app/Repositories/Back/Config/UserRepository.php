<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Config;
use App\Http\Requests\Back\Config\UserRequest;
use App\Jobs\WelcomeUserJob;
use App\Models\Civilite;
use App\Models\Locale;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public function getUsers(Request $request)
    {
        $currentUserId = auth('api')->user()->id;

        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('users')
            ->join('model_has_roles','users.id','=','model_has_roles.model_id')
            ->join('roles','model_has_roles.role_id','=','roles.id')
            ->join('civilites','users.civilite','=','civilites.id')
            ->selectRaw('users.id, users.nom, users.statut, email, telephone, roles.name as role, civilites.nom as civilite')
            ->where('users.id','<>',$currentUserId)
            ->where(function ($query) use($searchTerm){
                $query->whereRaw('LOWER(users.nom) like ? ',[ '%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(telephone) like ? ',[ '%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(email) like ? ',[ '%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(roles.name) like ? ',[ '%' . mb_strtolower($searchTerm) . '%']);
            })
            ->whereNull('users.deleted_at')
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    /**
     * Return ressource
     */
    public function getUser($id, $type = 'edit')
    {
        if($type == 'show'){
            $user = DB::table('users')
                ->join('model_has_roles','users.id','=','model_has_roles.model_id')
                ->join('roles','model_has_roles.role_id','=','roles.id')
                ->join('civilites','users.civilite','=','civilites.id')
                ->join('locales','users.locale','=','locales.id')
                ->selectRaw('users.id, users.nom, email, telephone, roles.name as role, civilites.nom as civilite, locales.libelle as locale, users.statut')
                ->where('users.id','=', $id)
                ->first();

            return ['user' => $user, 'role' => null];
        }

        $user = User::findOrFail($id);
        $role = $user->roles()->first();

        return ['user' => $user, 'role' => $role];
    }

    public function getRequirements(){

        $roles = Role::orderBy('name')->get(['id','name']);
        $locales = Locale::WhereNull('deleted_at')->orderBy('code')->get(['id','libelle']);
        $civilites = Civilite::WhereNull('deleted_at')->orderBy('nom')->get(['id','nom']);

        return ['role' => $roles, 'civilite' => $civilites, 'locale' => $locales];
    }

    public function store(UserRequest $request)
    {
        DB::beginTransaction();
        try {

            $passw = User::generatePassword();

            $user = User::create([
                'nom' => $request->input('nom'),
                'email' => $request->input('email'),
                'telephone' => $request->input('telephone'),
                'civilite' => $request->input('civilite'),
                'locale' => $request->input('locale'),
                'password' => Hash::make($passw),
                'statut' => filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN),
                'created_by' =>  auth()->user()->id,
                'updated_by' =>  auth()->user()->id,
            ]);

            $role = Role::where('id',$request->input('role'))->first();
            $user->assignRole($role);

            $job = (new WelcomeUserJob($user->id, $passw));
            dispatch($job);

            DB::commit();

            return true;
        }catch (\Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
        }

        return false;
    }

    public function update(UserRequest $request, $id)
    {
            DB::beginTransaction();
            try {
                $user = User::findOrFail($id);

                $user->nom = $request->input('nom');
                $user->email = $request->input('email');
                $user->telephone = $request->input('telephone');
                $user->civilite = $request->input('civilite');
                $user->locale = $request->input('locale');
                $user->statut = filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN);
                $user->updated_by = auth()->user()->id;
                $user->save();

                DB::table('model_has_roles')->where('model_id',$id)->delete();
                $user->assignRole($request->input('role'));

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
            $users = User::whereIn('id', $arrayId)->get();

            foreach ($users as $user){
                if(!$user->delete()){
                    $erreur = true;
                    break;
                }else{
                    $user->telephone = $user->telephone.$user->id;
                    $user->email = $user->email.$user->id;
                    $user->save();
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
