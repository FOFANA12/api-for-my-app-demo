<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\CustomerPasswordResetNotification;
use App\Notifications\UserPasswordResetNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{

    public function userForgetPassword(Request $request)
    {
        $attributeName = array(
            'email' => __('email'),
        );
        $validator = Validator::make($request->all(),[
            'email' => 'bail|required|string|email',
        ]);

        $validator->setAttributeNames($attributeName);

        if($validator->fails()){
            return response()->json(['erreur'=>true,'erreurs'=>$validator->validate()]);
        }else{
            $user = User::where('email', $request->input('email'))->where('statut', true)->first();

            if(!$user){
                return response()->json(['error' => true, 'errors' => ['email' => [__("Nous n'avons pas trouvé un utilisateur avec cette adresse e-mail.")]]], 422);
            }

            DB::beginTransaction();
            try {

                $passwordReset = PasswordReset::updateOrCreate(
                    ['email' => $user->email],
                    [
                        'email' => $user->email,
                        'token' => str_random(60),
                        'expire_at' => Carbon::now()->addMinutes(60),
                    ]
                );

                if ($user && $passwordReset)
                    $user->notify(new UserPasswordResetNotification($passwordReset->token));

                DB::commit();

                return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => 'Nous vous avons envoyé un lien de réinitialisation de mot de passe par email! Merci de consulter vos emails.', 'color'=>'success']]);

            }catch (\Exception $e){
                DB::rollback();
            }

            return response()->json(['error' => true, 'errors' => ['email' => [__("Échec de l'envoi du lien de réinitialisation du mot de passe, veuillez réessayer.")]]], 422);
        }
    }

    public function customerForgetPassword(Request $request)
    {
        $attributeName = array(
            'email' => __('email'),
        );
        $validator = Validator::make($request->all(),[
            'email' => 'bail|required|string|email',
        ]);

        $validator->setAttributeNames($attributeName);

        if($validator->fails()){
            return response()->json(['erreur'=>true,'erreurs'=>$validator->validate()]);
        }else{
            $customer = Customer::where('email', $request->input('email'))->where('statut', true)->first();

            if(!$customer){
                return response()->json(['error' => true, 'errors' => ['email' => [__("Nous n'avons pas trouvé un client avec cette adresse e-mail.")]]], 422);
            }

            DB::beginTransaction();
            try {

                $passwordReset = PasswordReset::updateOrCreate(
                    ['email' => $customer->email],
                    [
                        'email' => $customer->email,
                        'token' => str_random(60),
                        'expire_at' => Carbon::now()->addMinutes(60),
                    ]
                );

                if ($customer && $passwordReset)
                    $customer->notify(new CustomerPasswordResetNotification($passwordReset->token));

                DB::commit();

                return response()->json(['erreur' => false, 'type' => 'success', 'message' => 'Nous vous avons envoyé par e-mail le lien de réinitialisation de votre mot de passe ! Veuillez vérifier votre courriel.']);

            }catch (\Exception $e){
                DB::rollback();
            }

            return response()->json(['error' => true, 'errors' => ['email' => [__("Échec de l'envoi du lien de réinitialisation du mot de passe, veuillez réessayer.")]]], 422);
        }
    }
}
