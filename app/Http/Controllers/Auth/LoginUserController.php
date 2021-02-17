<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\CookieHelpers;
use App\Helpers\UUIDHelpers;
use App\Http\Controllers\Controller;
use App\Models\Civilite;
use App\Models\Locale;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LoginUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    public $successStatus = 200;

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $data = [
            'email' => $request->input('email'),
            'password'  =>  $request->input('password'),
            'statut' => true
        ];

        if (! $token = Auth::guard('user')->attempt($data)) {
            return $this->sendFailedLoginResponse($request);
        }

        $user = auth('user')->user();

        $tokenCookie = CookieHelpers::getCookie('token_user', $token, config('jwt.refresh_ttl'));

        return response()->json([
            'user' => ['id' => $user->id, 'nom'=>$user->nom, 'telephone'=>$user->telephone, 'email'=>$user->email, 'image'=>$user->image],
        ], 200)->withCookie($tokenCookie);

    }

    public function getProfile()
    {
        $user = auth('user')->user();
        $role = $user->roles()->first();

        $locales = Locale::WhereNull('deleted_at')->orderBy('code')->get(['id','libelle']);
        $civilites = Civilite::WhereNull('deleted_at')->orderBy('nom')->get(['id','nom']);

        return response()->json([
            'user' => $user,
            'role' => $role ? $role->name : '###',
            'locales' => $locales,
            'civilites' => $civilites,
        ]);
    }


    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $attributeName = array(
            'nom' => __('nom utilisateur'),
            'email' => __('email'),
            'telephone' => __('téléphone'),
            'password' => __('mot de passe'),
            'civilite' => __('civilité'),
            'locale' => __('langue'),
            'image' => __('image'),
        );

        $validator = Validator::make($request->all(),[
            'nom' => 'bail|required|string|max:50',
            'email' => 'bail|required|string|email|max:50|unique:users,email,'.$id,
            'telephone' => 'bail|nullable|string|max:15|unique:users,telephone,'.$id,
            'password' => 'bail|nullable|string|min:8|confirmed',
            'civilite' => 'bail|required|exists:civilites,id',
            'locale' => 'bail|required|exists:locales,id',
            'image' => 'bail|nullable|image|max:5120|mimes:jpeg,png,jpg'
        ]);

        $validator->setAttributeNames($attributeName);

        if($validator->fails()){
            return response()->json(['erreur'=>true,'erreurs'=>$validator->validate()]);
        }else {

            DB::beginTransaction();
            try {

                $name = null;

                if($request->hasFile('image')){

                    $originalImage= $request->file('image');
                    $name = UUIDHelpers::getUUID().'.'.$originalImage->getClientOriginalExtension();

                    Storage::disk('public')->put('avatars/'.$name, file_get_contents($originalImage));

                    $user->image = $name;
                }

                if(filter_var($request->input('clearImage'), FILTER_VALIDATE_BOOLEAN) == true){
                    $user->image = null;
                }

                $user->nom = $request->input('nom');
                $user->email = $request->input('email');
                $user->telephone = $request->input('telephone');
                $user->civilite = $request->input('civilite');
                $user->locale = $request->input('locale');
                $user->updated_by = auth()->user()->id;

                if(!empty($request->input('password'))){
                    $user->password = Hash::make($request->input('password'));
                }
                $user->save();

                DB::commit();

                return response()->json(['erreur' => false, 'user' => ['id' => $user->id, 'nom'=>$user->nom, 'telephone'=>$user->telephone, 'email'=>$user->email, 'image'=>$user->image], 'notify' => ['title' => null, 'text' => __('Votre profil a été enregistré avec succès'), 'color'=>'success']]);

            }catch (\Exception $e) {
                DB::rollback();
            }

            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => __('Désolé, des erreurs se sont produites lors du traitement.'), 'color'=>'danger']]);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth('user')->refresh();

        return response()->json(compact('token'));
    }

    public function me()
    {
        $user = auth('user')->user();

        return response()->json(['user' => $user]);
    }

    /*
    public function forgetPassword(Request $request)
    {
        $user = User::where('email', $request->input('email'))->where('statut', true)->first();
        if(!$user){
            return response()->json(['error' => true, 'errors' => ['email' => __('Your email adress was not found.')]], 401);
        }

        try{
            Password::sendResetLink($request->input('email'));
        }catch (\Exception $e){
            return response()->json(['error' => true, 'errors' => ['statut' => __($e->getMessage())]], 401);
        }

        return response()->json(['error' => false, 'data' => ['message' => __('A reset email has been sent! Please check your email.')]], 200);
    }

    public function resetPassword(Request $request)
    {
        $attributeName = array(
            'email' => __('email'),
            'password' => __('password'),
        );
        $validator = Validator::make($request->all(),[
            'email' => 'bail|required|string|email|max:50|exists:users,email',
            'password' => 'bail|required|string|min:8|confirmed',
        ]);

        $validator->setAttributeNames($attributeName);

        if($validator->fails()){
            return response()->json(['erreur'=>true,'erreurs'=>$validator->validate()]);
        }else{
            try{
                $user = User::where('email', $request->input('email'))->first();
                $user->password = Hash::make($request->input('password'));
                $user->save();

            }catch(\Exception $e){
                return response()->json(['error' => true, 'errors' => ['statut' => __('Failed to change password, please try again.')]], 401);
            }
        }

        return response()->json(['error' => false, 'data' => ['message' => __('Password successfully changed..')]], 200);
    }
*/
    public function logout(Request $request)
    {
        auth('user')->logout();

        if ($request->hasCookie('token_user') ){

            $cookieToken = \Cookie::forget('token_user');
            Log::info('delete session');

            return response()->json(['message' => 'Déconnexion réussie'])
                ->withCookie($cookieToken);
        }

        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
