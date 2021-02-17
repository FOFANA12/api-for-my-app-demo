<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\CookieHelpers;
use App\Helpers\UUIDHelpers;
use App\Http\Controllers\Controller;
use App\Models\Civilite;
use App\Models\Customer;
use App\Models\JWT;
use App\Models\Locale;
use App\Models\MemberStatut;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
class LoginCustomerController extends Controller
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


        if (! $token = Auth::guard('customer')->attempt($data)) {
            return $this->sendFailedLoginResponse($request);
        }


        $customer = auth('customer')->user();

        $tokenCookie = CookieHelpers::getCookie('token_customer', $token, config('jwt.refresh_ttl'));

        return response()->json([
            'user' => ['id' => $customer->id, 'nom'=>$customer->nom, 'prenom'=>$customer->prenom, 'telephone'=>$customer->telephone, 'email'=>$customer->email, 'image'=>$customer->image],
        ], 200)->withCookie($tokenCookie);

    }

    public function getProfile()
    {
        $user = auth('customer')->user();

        $locales = Locale::WhereNull('deleted_at')->orderBy('code')->get(['id','libelle']);
        $civilites = Civilite::WhereNull('deleted_at')->orderBy('nom')->get(['id','nom']);
        $membershipStatut = MemberStatut::Where('id',$user->member_statut)->first();

        return response()->json([
            'user' => $user,
            'locales' => $locales,
            'civilites' => $civilites,
            'membershipStatut' => $membershipStatut,
        ]);
    }

    public function updateProfile(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $attributeName = array(
            'nom' => __('nom'),
            'prenom' => __('prenom'),
            'entreprise' => __('entreprise'),
            'adresse' => __('adresse'),
            'email' => __('email'),
            'telephone' => __('téléphone'),
            'num_passeport' => __('n° passeport'),
            'condition_medical' => __('condition médicale'),
            'contact_urgence_nom' => __('nom contact d\'urgence'),
            'contact_urgence_telephone' => __('téléphone contact d\'urgence'),
            'civilite' => __('civilité'),
            'locale' => __('langue'),
            'passeport_file' => __('fichier passeport'),
            'image' => __('image'),
            'password' => __('mot de passe'),
        );

        $validator = Validator::make($request->all(),[
            'nom' => 'bail|required|string|max:50',
            'prenom' => 'bail|required|string|max:50',
            'entreprise' => 'bail|nullable|max:30',
            'adresse' => 'bail|nullable|max:100',
            'email' => 'bail|required|string|email|max:50|unique:customers,email,'.$id,
            'telephone' => 'bail|nullable|string|max:15|unique:customers,telephone,'.$id,
            'num_passeport' => 'bail|nullable|max:15',
            'passeport_file' => 'bail|nullable|file|max:3072|mimes:jpeg,png,jpg,pdf',
            'condition_medical' => 'bail|nullable|max:50',
            'contact_urgence_nom' => 'bail|nullable|max:50',
            'contact_urgence_telephone' => 'bail|nullable|max:15',
            'civilite' => 'bail|required|exists:civilites,id',
            'locale' => 'bail|required|exists:locales,id',
            'password' => 'bail|nullable|string|min:8|confirmed',
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
                    $name = 'c'.UUIDHelpers::getUUID().'.'.$originalImage->getClientOriginalExtension();
                    Storage::disk('public')->put('avatars/'.$name, file_get_contents($originalImage));

                    $customer->image = $name;
                }

                if(filter_var($request->input('clearImage'), FILTER_VALIDATE_BOOLEAN) == true){
                    $customer->image = null;
                }

                if($request->hasFile('passeport_file')){

                    $originalFichier= $request->file('passeport_file');
                    $passeport_file = 'c'.UUIDHelpers::getUUID().'.'.$originalFichier->getClientOriginalExtension();

                    Storage::disk('public')->put('uploads/'.$passeport_file, file_get_contents($originalFichier));

                    $customer->passeport_file = $passeport_file;
                    $customer->passeport_file_name = Str::limit($originalFichier->getClientOriginalName(),'97','...');
                }

                if(filter_var($request->input('clearFilePassport'), FILTER_VALIDATE_BOOLEAN) == true){
                    $customer->passeport_file = null;
                    $customer->passeport_file_name = null;
                }

                $customer->nom = $request->input('nom');
                $customer->prenom = $request->input('prenom');
                $customer->civilite = $request->input('civilite');
                $customer->locale = $request->input('locale');
                $customer->entreprise = $request->input('entreprise');
                $customer->email = $request->input('email');
                $customer->telephone = $request->input('telephone');
                $customer->condition_medical = $request->input('condition_medical');
                $customer->contact_urgence_nom = $request->input('contact_urgence_nom');
                $customer->contact_urgence_telephone = $request->input('contact_urgence_telephone');
                $customer->num_passeport = $request->input('num_passeport');
                $customer->adresse = $request->input('adresse');

                if(!empty($request->input('password'))){
                    $customer->password = Hash::make($request->input('password'));
                }
                $customer->save();

                DB::commit();

                return response()->json(['erreur' => false, 'user' => ['id' => $customer->id, 'nom'=>$customer->nom, 'prenom'=>$customer->prenom, 'telephone'=>$customer->telephone, 'email'=>$customer->email, 'image'=>$customer->image], 'type' => 'success', 'message' => __('Votre profil a été enregistré avec succès')]);

            }catch (\Exception $e) {
                Log::info($e->getMessage());
                DB::rollback();
            }

            return response()->json(['erreur' => true, 'type' => 'error', 'message' => __('Désolé, des erreurs se sont produites lors du traitement.')]);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth('customer')->refresh();

        return response()->json(compact('token'));
    }

    public function me()
    {
        $user = auth('customer')->user();

        return response()->json(['user' => $user]);
    }

    public function logout(Request $request)
    {
        auth('customer')->logout();

        if ($request->hasCookie('token_customer') ){

            $cookieToken = \Cookie::forget('token_customer');
            Log::info('delete session');

            return response()->json(['message' => 'Déconnexion réussie'])
                ->withCookie($cookieToken);
        }

        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
