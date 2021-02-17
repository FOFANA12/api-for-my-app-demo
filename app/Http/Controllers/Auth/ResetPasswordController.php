<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    public function userResetPassword(Request $request)
    {
        $attributeName = array(
            'email' => __('email'),
            'password' => __('password'),
            'token' => __('token'),
        );

        $validator = Validator::make($request->all(),[
            'email' => 'bail|required|string|email|max:50|exists:users,email',
            'password' => 'bail|required|string|min:8|confirmed',
            'token' => 'required|string'
        ]);

        $validator->setAttributeNames($attributeName);

        if($validator->fails()){
            return response()->json(['erreur'=>true,'erreurs'=>$validator->validate()]);
        }else{
            DB::beginTransaction();

            try{
                $passwordReset = PasswordReset::where([
                    ['token', $request->token],
                    ['email', $request->email]
                ])->first();

                if (!$passwordReset)
                    return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => "Ce jeton de réinitialisation de mot de passe n'est plus valide.", 'color'=>'danger']], 200);

                if(Carbon::parse($passwordReset->expire_at)->lte(Carbon::now()))
                    return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => 'Ce jeton de réinitialisation de mot de passe a expiré.', 'color'=>'danger']], 200);

                $user = User::where('email', $request->input('email'))->first();

                if(!$user){
                    return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => "Nous n'avons pas trouvé un utilisateur avec cette adresse e-mail.", 'color'=>'danger']], 404);
                }

                $user->password = Hash::make($request->input('password'));
                $user->save();

                $passwordReset->delete();

                DB::commit();

                return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => 'Le mot de passe a été modifié avec succès.', 'color'=>'success']], 200);

            }catch(\Exception $e){
                DB::rollback();
            }
        }

        return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => 'Echec de changement du mot de passe, veuillez réessayer.', 'color'=>'success']], 401);
    }

    public function customerResetPassword(Request $request)
    {
        $attributeName = array(
            'email' => __('email'),
            'password' => __('password'),
            'token' => __('token'),
        );
        $validator = Validator::make($request->all(),[
            'email' => 'bail|required|string|email|max:50|exists:customers,email',
            'password' => 'bail|required|string|min:8|confirmed',
            'token' => 'required|string'
        ]);

        $validator->setAttributeNames($attributeName);

        if($validator->fails()){
            return response()->json(['erreur'=>true,'erreurs'=>$validator->validate()]);
        }else{
            DB::beginTransaction();

            try{
                $passwordReset = PasswordReset::where([
                    ['token', $request->token],
                    ['email', $request->email]
                ])->first();

                if (!$passwordReset)
                    return response()->json(['erreur' => true, 'type' => 'error', 'message' => "Ce jeton de réinitialisation de mot de passe n'est plus valide."], 200);

                if(Carbon::parse($passwordReset->expire_at)->lte(Carbon::now()))
                    return response()->json(['erreur' => true, 'type' => 'error', 'message' => 'Ce jeton de réinitialisation de mot de passe a expiré.'], 200);

                $customer = Customer::where('email', $request->input('email'))->first();

                if(!$customer){
                    return response()->json(['erreur' => true, 'type' => 'error', 'message' => "Nous n'avons pas trouvé un client avec cette adresse e-mail."], 404);
                }

                $customer->password = Hash::make($request->input('password'));
                $customer->save();

                $passwordReset->delete();

                DB::commit();

                return response()->json(['erreur' => false, 'type' => 'success', 'message' => 'Le mot de passe a été modifié avec succès.'], 200);

            }catch(\Exception $e){
                DB::rollback();
            }
        }

        return response()->json(['erreur' => true, 'type' => 'error', 'message' => 'Echec de changement du mot de passe, veuillez réessayer.'], 401);
    }

}
