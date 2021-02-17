<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Customer;
use App\Helpers\UUIDHelpers;
use App\Http\Requests\Back\Customer\CustomerRequest;
use App\Jobs\WelcomeCustomerJob;
use App\Models\Civilite;
use App\Models\CommandeRepas;
use App\Models\Customer;
use App\Models\Locale;
use App\Models\MemberStatut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerRepository
{
    public function getCustomers(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $rows = DB::table('customers')
            ->join('member_statuts','customers.member_statut','=','member_statuts.id')
            ->select(
                DB::raw("customers.id, customers.nom, customers.prenom, customers.entreprise, customers.adresse, customers.statut")
            )
            ->where(function ($query) use($searchTerm){
                $query->whereRaw('LOWER(customers.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(customers.prenom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(customers.entreprise) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(customers.adresse) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
            })
            ->whereNull('customers.deleted_at')
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    /**
     * Return ressource
     */
    public function getRequirements()
    {
        $locales = Locale::WhereNull('deleted_at')->where('statut', true)->orderBy('libelle')->get(['id','libelle']);
        $civilites = Civilite::WhereNull('deleted_at')->where('statut', true)->orderBy('nom')->get(['id','nom']);
        $statuts = MemberStatut::WhereNull('deleted_at')->where('statut', true)->orderBy('nom')->get(['id','nom']);

        return ['locales' => $locales, 'civilites' => $civilites, 'statuts' => $statuts];
    }

    /**
     * Return ressource
     */
    public function getCustomer($id, $type = 'edit')
    {
        if($type == 'show'){
            $customer = DB::table('customers')
                ->join('civilites','customers.civilite','=','civilites.id')
                ->join('locales','customers.locale','=','locales.id')
                ->join('member_statuts','customers.member_statut','=','member_statuts.id')
                ->selectRaw('customers.id, customers.nom, customers.prenom, civilites.nom as civilite, locales.libelle as locale, member_statuts.nom as member_statut, customers.entreprise, customers.email, customers.telephone, condition_medical, contact_urgence_nom,
                  contact_urgence_telephone, customers.statut, passeport_file_name, passeport_file')
                ->where('customers.id','=',$id)
                ->first();


            return $customer;
        }

        $customer = Customer::findOrFail($id);

        return $customer;
    }

    public function store(CustomerRequest $request)
    {
        DB::beginTransaction();
        try {

            $passeport_file = null;
            $passw = Customer::generatePassword();

            $customer = Customer::create([
                'nom' => $request->input('nom'),
                'prenom' => $request->input('prenom'),
                'entreprise' => $request->input('entreprise'),
                'adresse' => $request->input('adresse'),
                'member_statut' => $request->input('member_statut'),
                'email' => $request->input('email'),
                'telephone' => $request->input('telephone'),
                'num_passeport' => $request->input('num_passeport'),
                'password' => Hash::make($passw),
                'condition_medical' => $request->input('condition_medical'),
                'contact_urgence_nom' => $request->input('contact_urgence_nom'),
                'contact_urgence_telephone' => $request->input('contact_urgence_telephone'),
                'civilite' => $request->input('civilite'),
                'locale' => $request->input('locale'),
                'statut' => filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN),
                'created_by' =>  auth()->user()->id,
                'updated_by' =>  auth()->user()->id,
            ]);

            if($request->hasFile('passeport_file')){

                $originalFichier= $request->file('passeport_file');
                $passeport_file = 'c'.UUIDHelpers::getUUID().'.'.$originalFichier->getClientOriginalExtension();

                Storage::disk('public')->put('uploads/'.$passeport_file, file_get_contents($originalFichier));

                $customer->passeport_file = $passeport_file;
                $customer->passeport_file_name = Str::limit($originalFichier->getClientOriginalName(),'97','...');
                $customer->save();
            }


            $job = (new WelcomeCustomerJob($customer->id, $passw));
            dispatch($job);

            DB::commit();

            return true;
        }catch (\Exception $e) {
            DB::rollback();
        }

        return false;
    }

    public function update(CustomerRequest $request, $id)
    {
            DB::beginTransaction();
            try {

                $passeport_file = null;
                $customer = Customer::findOrFail($id);

                $customer->nom = $request->input('nom');
                $customer->prenom = $request->input('prenom');
                $customer->entreprise = $request->input('entreprise');
                $customer->adresse = $request->input('adresse');
                $customer->member_statut = $request->input('member_statut');
                $customer->email = $request->input('email');
                $customer->telephone = $request->input('telephone');
                $customer->num_passeport = $request->input('num_passeport');
                $customer->condition_medical = $request->input('condition_medical');
                $customer->contact_urgence_nom = $request->input('contact_urgence_nom');
                $customer->contact_urgence_telephone = $request->input('contact_urgence_telephone');
                $customer->civilite = $request->input('civilite');
                $customer->locale = $request->input('locale');
                $customer->statut = filter_var($request->input('statut'), FILTER_VALIDATE_BOOLEAN);
                $customer->updated_by =  auth()->user()->id;

                if($request->hasFile('passeport_file')){

                    $originalFichier= $request->file('passeport_file');
                    $passeport_file = 'c'.UUIDHelpers::getUUID().'.'.$originalFichier->getClientOriginalExtension();

                    Storage::disk('public')->put('uploads/'.$passeport_file, file_get_contents($originalFichier));

                    $customer->passeport_file = $passeport_file;
                    $customer->passeport_file_name = Str::limit($originalFichier->getClientOriginalName(),'97','...');
                }

                if(filter_var($request->input('clearImage'), FILTER_VALIDATE_BOOLEAN) == true){
                    $customer->passeport_file = null;
                    $customer->passeport_file_name = null;
                }

                $customer->save();

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
            $customers = Customer::whereIn('id', $arrayId)->get();


            foreach ($customers as $customer){
                if(!$customer->delete()){
                    $erreur = true;
                    break;
                }else{
                    $customer->email = $customer->email.$customer->id;
                    $customer->telephone = $customer->telephone.$customer->id;
                    $customer->save();
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
