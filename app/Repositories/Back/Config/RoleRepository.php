<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Config;
use App\Http\Requests\Back\Config\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleRepository
{
    public function getRoles(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('roles')
            ->select(
                DB::raw("id, name"),
                DB::raw("IFNULL((SELECT nom FROM users WHERE roles.created_by = users.id ),'###') as created_by"),
                DB::raw("IFNULL((SELECT nom FROM users WHERE roles.updated_by = users.id ),'###') as updated_by")
            )
            ->where(function ($query) use($searchTerm){
                $query->whereRaw('LOWER(name) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
            })
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;

    }

    public function getPermissions(){

        $permissions = Permission::all()->toArray();
        $permissions = $this->group_by("perm_group", $permissions);

        return $permissions;
    }

    /**
     * Return ressource
     */
    public function getRole($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all()->toArray();
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)->pluck('permission_id');

        $permissions = $this->group_by("perm_group", $permissions);

        return ['role' => $role, 'permissions'=>$permissions, 'rolePermissions'=>$rolePermissions];
    }

    public function store(RoleRequest $request)
    {
        DB::beginTransaction();
        try {

            $role = Role::create([
                'name' => $request->input('role'),
                'created_by' =>  auth()->user()->id,
                'updated_by' =>  auth()->user()->id,
            ]);

            if($request->input('permissions'))
                $role->permissions()->sync($request->input('permissions'));

            DB::commit();

            return true;
        }catch (\Exception $e) {
            DB::rollback();
        }

        return false;
    }

    public function update(RoleRequest $request, $id)
    {
            DB::beginTransaction();
            try {
                $role = Role::findOrFail($id);

                $role->name = $request->input('role');
                $role->updated_by = auth()->user()->id;
                $role->save();

                if($request->input('permissions'))
                    $role->permissions()->sync($request->input('permissions'));
                else
                    $role->permissions()->detach();

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
            $roles = Role::whereIn('id', $arrayId)->get();

            foreach ($roles as $role){
                $countUser = User::with('roles')->whereHas('roles',function ($q) use($role){
                    $q->where("id","=",$role->id);
                })->count();

                if($countUser>0 || !$role->delete()){
                    $erreur = true;
                    break;
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


    private function group_by($key, $data) {
        $result = array();

        foreach($data as $val) {
            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = $val;
            }else{
                $result[""][] = $val;
            }
        }

        return $result;
    }
}
