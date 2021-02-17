<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Customer;
use App\Helpers\ConfigApp;
use App\Http\Requests\Back\Customer\CustomerNoteRequest;
use App\Models\CustomerNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CustomerNoteRepository
{
    public function getNotes(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');
        $customer = $request->get('customer');

        $rows = [];
        if($customer){
            $rows = DB::table('customer_notes')
                ->join('customers','customer_notes.customer','=','customers.id')
                ->select(
                    DB::raw("customer_notes.id, customers.id as customer_id, customer_notes.date, customer_notes.sujet, customer_notes.commentaire")
                )
                ->where(function ($query) use($searchTerm){
                    $query->whereRaw('LOWER(customer_notes.sujet) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(customer_notes.commentaire) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
                })
                ->whereNull('customer_notes.deleted_at')
                ->whereNull('customers.deleted_at')
                ->where('customers.id','=',$customer)
                ->orderBy($sortColumn,$sortType)
                ->paginate($perPage);
        }


        return $rows;
    }

    /**
     * Return ressource
     */
    /*public function getRequirements()
    {
        return Restaurant::WhereNull('deleted_at')->orderBy('nom')->get(['id','nom']);
    }*/

    /**
     * Return ressource
     */
    public function getNote($id, $type = 'edit')
    {
        if($type == 'show'){
            $customerNote = DB::table('customer_notes')
                ->join('customers','customer_notes.customer','=','customers.id')
                ->selectRaw('customer_notes.id, customer_notes.date, customer_notes.sujet, commentaire, CONCAT(customers.nom, " ", customers.prenom) as customer')
                ->where('customer_notes.id','=', $id)
                ->first();

            return $customerNote;
        }

        return CustomerNote::findOrFail($id);
    }

    public function store(CustomerNoteRequest $request)
    {
        $date_format_php = ConfigApp::date_format_php();

        DB::beginTransaction();
        try {
            $name = null;

            CustomerNote::create([
                'customer' => $request->input('customer'),
                'date' => Carbon::createFromFormat($date_format_php, $request->input('date')),
                'sujet' => $request->input('sujet'),
                'commentaire' => $request->input('commentaire'),
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

    public function update(CustomerNoteRequest $request, $id)
    {
        $date_format_php = ConfigApp::date_format_php();

        DB::beginTransaction();
            try {
                $customerNote = CustomerNote::findOrFail($id);

                $customerNote->sujet = $request->input('sujet');
                $customerNote->date = Carbon::createFromFormat($date_format_php, $request->input('date'));
                $customerNote->commentaire = $request->input('commentaire');
                $customerNote->updated_by = auth()->user()->id;
                $customerNote->save();

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
            $customerNotes = CustomerNote::whereIn('id', $arrayId)->get();


            foreach ($customerNotes as $customerNote){
                if(!$customerNote->delete()){
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
}
