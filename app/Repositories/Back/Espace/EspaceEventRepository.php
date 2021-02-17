<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Espace;
use App\Helpers\ConfigApp;
use App\Http\Requests\Back\Espace\EspaceEventRequest;
use App\Models\EspaceEvent;
use App\Models\EspaceInactivePeriode;
use App\Models\EspaceReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EspaceEventRepository
{
    public function getEspaceEvents(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $espace = $request->get('espace');
        $filterType = $request->get('filterType');

        /*
         * 1 == reservation en attentes
         * 2 == reservation confirmée
         * 3 == reservation refusée
         *
         * 4 == inactive periode
         * */
        $perPage = $request->get('perPage');
        $rows = [];

        if($espace){
            if($filterType == 1 || $filterType == 2 || $filterType == 3){// reservation en attente || // reservation confirmée || // reservation refusée
                $rows = DB::table('espace_reservations')
                    ->join('espace_events','espace_reservations.espace_event','=','espace_events.id')
                    ->join('customers','espace_reservations.customer','=','customers.id')
                    ->join('espaces','espace_events.espace','=','espaces.id')
                    ->select(
                        DB::raw("espace_events.id, start_date, end_date, CONCAT(customers.nom, ' ', customers.prenom) as customer, espaces.nom as espace, espace_reservations.statut")
                    )
                    ->where(function ($query) use($searchTerm){
                        $query->whereRaw('LOWER(customers.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                            ->orWhereRaw('LOWER(customers.prenom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                            ->orWhereRaw('LOWER(espaces.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
                    })
                    ->whereNull('espace_events.deleted_at')
                    ->where('espaces.id','=',$espace)
                    ->where('espace_reservations.statut','=',$filterType)
                    ->orderBy('espace_events.'.$sortColumn,$sortType)
                    ->paginate($perPage);
            }else if($filterType == 4){ //inactive periode
                $rows = DB::table('espace_inactive_periodes')
                    ->join('espace_events','espace_inactive_periodes.espace_event','=','espace_events.id')
                    ->join('espaces','espace_events.espace','=','espaces.id')
                    ->select(
                        DB::raw("espace_events.id, start_date, end_date, text, espaces.nom as espace")
                    )
                    ->where(function ($query) use($searchTerm){
                        $query->whereRaw('LOWER(espace_inactive_periodes.text) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                            ->orWhereRaw('LOWER(espaces.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%']);
                    })
                    ->whereNull('espace_events.deleted_at')
                    ->where('espaces.id','=',$espace)
                    ->orderBy('espace_events.'.$sortColumn,$sortType)
                    ->paginate($perPage);
            }

        }

        return $rows;
    }

    /**
     * Return ressource
     */
    public function getRequirements()
    {
        /*$espaces = DB::table('espaces')
                    ->select(
                        DB::raw("espaces.id, espaces.nom, max_people"),
                        DB::raw("(SELECT member_statuts.nom FROM member_statuts, espace_statuts WHERE espace_statuts.espace = espaces.id AND  espace_statuts.statut = member_statuts.id AND espace_statuts.deleted_at IS NULL LIMIT 1) as espace_statut")
                    )
                    ->where('statut','=',true)
                    ->whereNull('deleted_at')
                    ->orderBy('nom')
                    ->get();*/

        $customers = DB::table('customers')
            ->selectRaw("id, CONCAT(nom,' ', prenom) as nom")
            ->orderBy('nom')
            ->where('statut','=',true)
            ->whereNull('deleted_at')
            ->get();

        return $customers;
    }

    /**
     * Return ressource
     */
    public function getSpacePlanning($id)
    {
        $reservations = DB::table('espace_reservations')
            ->join('espace_events','espace_reservations.espace_event','=','espace_events.id')
            ->join('customers','espace_reservations.customer','=','customers.id')
            ->join('espaces','espace_events.espace','=','espaces.id')
            ->select(
                DB::raw("espace_events.id, DATE_FORMAT(start_date, '%Y-%m-%d %H:%i') as start_date, DATE_FORMAT(end_date, '%Y-%m-%d %H:%i') as end_date, CONCAT(customers.nom, ' ', customers.prenom) as customer, espaces.nom as espace, espace_reservations.statut")
            )
            ->whereNull('espace_events.deleted_at')
            ->where('espaces.id','=',$id)
            ->whereIn('espace_reservations.statut',[1,2])
            ->get();

        $inactivePeriodes = DB::table('espace_inactive_periodes')
            ->join('espace_events','espace_inactive_periodes.espace_event','=','espace_events.id')
            ->join('espaces','espace_events.espace','=','espaces.id')
            ->select(
                DB::raw("espace_events.id, DATE_FORMAT(start_date, '%Y-%m-%d %H:%i') as start_date, DATE_FORMAT(end_date, '%Y-%m-%d %H:%i') as end_date, text, espaces.nom as espace")
            )
            ->whereNull('espace_events.deleted_at')
            ->where('espaces.id','=',$id)
            ->get();

        return ['reservations' => $reservations, 'inactivePeriodes' => $inactivePeriodes];
    }


    public function store(EspaceEventRequest $request)
    {

        $date_format_php = ConfigApp::date_format_php().' '.ConfigApp::heure_format_php();

        DB::beginTransaction();
        try {

            $espaceEvent = EspaceEvent::create([
                'espace' => $request->input('espace'),
                'start_date' => Carbon::createFromFormat($date_format_php, $request->input('start_date')),
                'end_date' => Carbon::createFromFormat($date_format_php, $request->input('end_date')),
                'created_by' =>  auth()->user()->id,
                'updated_by' =>  auth()->user()->id,
            ]);

            if($request->input('store_type') == 'reservation'){
                EspaceReservation::create([
                    'espace_event' => $espaceEvent->id,
                    'customer' => $request->input('customer'),
                ]);
            }else{
                EspaceInactivePeriode::create([
                    'espace_event' => $espaceEvent->id,
                    'text' => $request->input('description'),
                ]);
            }


            DB::commit();

            return true;
        }catch (\Exception $e) {
            Log::info($e->getMessage());
            DB::rollback();
        }

        return false;
    }

    public function setStatutReservationSpace(Request $request, $id)
    {

        DB::beginTransaction();
        try {

            $espaceEvent = EspaceEvent::findOrFail($id);
            $espaceEvent->updated_by =  auth()->user()->id;

            $reservation = EspaceReservation::where('espace_event',$espaceEvent->id)->first();
            $reservation->statut = $request->input('statut');

            $reservation->save();
            $espaceEvent->save();

            DB::commit();

            return true;

        }catch (\Exception $e) {
            DB::rollback();
        }

        return false;
    }

   /* public function update(EspaceEventRequest $request, $id)
    {
        $date_format_php = ConfigApp::date_format_php();

        DB::beginTransaction();
            try {

                $reservation = EspaceEvent::findOrFail($id);


                $reservation->customer = $request->input('customer');
                $reservation->espace = $request->input('espace');
                $reservation->start_date = Carbon::createFromFormat($date_format_php, $request->input('start_date'));
                $reservation->end_date = Carbon::createFromFormat($date_format_php, $request->input('end_date'));
                $reservation->updated_by =  auth()->user()->id;
                $reservation->save();

                DB::commit();

                return true;

            }catch (\Exception $e) {
                DB::rollback();
            }

            return false;
    }*/

    public function destroy($array)
    {
        $erreur = false;

        DB::beginTransaction();
        try {
            $arrayId = explode(",", $array);
            $espaceEvents = EspaceEvent::whereIn('id', $arrayId)->get();

            foreach ($espaceEvents as $espaceEvent){
                if(!$espaceEvent->delete()){
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
