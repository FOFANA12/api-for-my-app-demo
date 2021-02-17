<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Event;
use App\Helpers\ConfigApp;
use App\Helpers\UUIDHelpers;
use App\Http\Requests\Back\Event\EventRequest;
use App\Jobs\SendInvitationEventJob;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\EventInvitation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventRepository
{
    public function getEvents(Request $request)
    {
        /*$event = Event::where('id','3a5ed27a-b59d-4f47-8550-9a37b8ab8f2d')->first();

        $job = (new SendInvitationEventJob($event->id));
        dispatch($job);*/

        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $date_format = ConfigApp::date_format_sql();
        $heure_format = ConfigApp::heure_format_sql();

        $rows = DB::table('events')
            ->select(
                DB::raw("events.id, events.nom, events.date, events.prix, events.max_invite, description, publier"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.participation = 1 AND event_invitations.approbation = 1) as nbre_invite_confirm")
            )
            ->where(function ($query) use($searchTerm, $date_format, $heure_format){
                $query->whereRaw('LOWER(events.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhere(DB::raw("DATE_FORMAT(events.date, '$date_format $heure_format')"), 'like', '%' . $searchTerm . '%');
            })
            ->whereNull('events.deleted_at')
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    /**
     * Return ressource
     */
    public function getRequirements()
    {
        $customers = Customer::WhereNull('deleted_at')->where('statut', true)->orderBy('nom')->get(['id','nom','prenom','email','telephone']);
        return $customers;
    }

    /**
     * Return ressource
     */
    public function getEvent($id, $type = 'edit')
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

        $event = Event::findOrFail($id);

        $eventFiles = DB::table('event_images')
                        ->selectRaw('event_images.id, file_id, null as file, file_name')
                        ->where('event','=',$id)
                        ->whereNull('event_images.deleted_at')
                        ->get();

        $invites = DB::table('event_invitations')
            ->join('customers','event_invitations.customer','=','customers.id')
            ->selectRaw('event_invitations.id, customer as customer_id, customers.nom, customers.prenom, email, telephone, participation, approbation, participation_date, approbation_date')
            ->where('event','=',$id)
            ->whereNull('event_invitations.deleted_at')
            ->get();

        return ['event' => $event, 'eventFiles' => $eventFiles, 'invites' => $invites];
    }

    public function setStatutPublication($id){
        DB::beginTransaction();
        try {

            $event = Event::findOrFail($id);

            $event->publier = !$event->publier;

            if($event->publication_date == null && $event->publier == true)
                $event->publication_date = Carbon::now();

            $event->save();


            if ($event->publier == true) {
                $job = (new SendInvitationEventJob("normal", null, $event->id));
                dispatch($job);
            }

            DB::commit();
            return [true, $event->publier];
        }catch (\Exception $e) {
            DB::rollback();
        }
        return [false];
    }

    public function setActionEvent(Request $request){
        $eventId = $request->get('event');
        $value = $request->get('value');
        $customerId = $request->get('customerId');
        $approbation_date = '';

        DB::beginTransaction();
        try {
            if($value == 1){//confirmation
                $invitation = EventInvitation::where('event',$eventId)->where('customer',$customerId)->first();
                if($invitation->approbation_date) return false;

                $invitation->approbation = 1;
                $approbation_date = Carbon::now();
                $invitation->approbation_date = $approbation_date;
                $invitation->save();
            }else if($value == -1){//waiting
                $invitation = EventInvitation::where('event',$eventId)->where('customer',$customerId)->first();

                $invitation->approbation = -1;
                $approbation_date = null;
                $invitation->approbation_date = $approbation_date;
                $invitation->save();
            }else if($value == 0){//refus
                $invitation = EventInvitation::where('event',$eventId)->where('customer',$customerId)->first();
                if($invitation->approbation_date) return false;

                $invitation->approbation = 0;
                $approbation_date = Carbon::now();
                $invitation->approbation_date = $approbation_date;
                $invitation->save();
            }else if($value == 2){//re-send email
                $job = (new SendInvitationEventJob("re-send-customer", $customerId, $eventId));
                dispatch($job);
            }

            DB::commit();

            return [true, $value, $approbation_date];
        }catch (\Exception $e) {
            DB::rollback();
        }
        return [false];
    }

    public function checkStepper(EventRequest $request)
    {
        return true;
    }

    public function store(EventRequest $request)
    {

        $date_format_php = ConfigApp::date_format_php().' '.ConfigApp::heure_format_php();
        DB::beginTransaction();
        try {

            $event = Event::create([
                'nom' => $request->input('nom'),
                'date' => Carbon::createFromFormat($date_format_php, $request->input('date')),
                'prix' => $request->input('prix'),
                'max_invite' => $request->input('max_invite'),
                'description' => $request->input('description'),
                'publier' => filter_var($request->input('publier'), FILTER_VALIDATE_BOOLEAN),
                'created_by' =>  auth()->user()->id,
                'updated_by' =>  auth()->user()->id,
            ]);


            if($request->input('invites')) {
                foreach ($request->input('invites') as $array) {

                    EventInvitation::create([
                        'event'=>$event->id,
                        'customer'=>$array['customer_id'],
                    ]);
                }
            }


            if($request->eventFiles) {
                foreach ($request->eventFiles as $array) {
                    $originalFichier= $array['file'];
                    $file_id = UUIDHelpers::getUUID().'.'.$originalFichier->getClientOriginalExtension();

                    Storage::disk('public')->put('uploads/'.$file_id, file_get_contents($originalFichier));

                    EventImage::create([
                        'event'=>$event->id,
                        'file_id'=> $file_id,
                        'file_name'=> Str::limit($originalFichier->getClientOriginalName(),'97','...'),
                    ]);
                }
            }

            if($event->publier == true){
                $event->publication_date = Carbon::now();
                $event->save();

                $job = (new SendInvitationEventJob("normal", null, $event->id));
                dispatch($job);
            }

            DB::commit();

            return true;
        }catch (\Exception $e) {
            DB::rollback();
        }

        return false;
    }

    public function update(EventRequest $request, $id)
    {
            $date_format_php = ConfigApp::date_format_php().' '.ConfigApp::heure_format_php();
            DB::beginTransaction();
            try {

                $event = Event::findOrFail($id);

                $event->nom = $request->input('nom');
                $event->date = Carbon::createFromFormat($date_format_php, $request->input('date'));
                $event->prix = $request->input('prix');
                $event->max_invite = $request->input('max_invite');
                $event->description = $request->input('description');
                $event->publier = filter_var($request->input('publier'), FILTER_VALIDATE_BOOLEAN);
                $event->updated_by =  auth()->user()->id;
                $event->save();

                $arrayId = array();
                foreach ($request->input('invites') as $array){
                    array_push($arrayId,$array['id']);
                }

                $arrayId = implode("','", $arrayId);
                EventInvitation::whereRaw("id NOT IN('$arrayId') AND event = '$event->id'")->delete();

                if($request->input('invites')) {
                    foreach ($request->input('invites') as $array) {
                        if(!$array['id']){//nouvel element
                            EventInvitation::create([
                                'event'=>$event->id,
                                'customer'=>$array['customer_id'],
                            ]);
                        }
                    }
                }

                $arrayId = array();
                foreach ($request->input('eventOldFiles') as $array){
                    array_push($arrayId,$array['id']);
                }
                $arrayId = implode("','", $arrayId);
                EventImage::whereRaw("id NOT IN('$arrayId') AND event = '$event->id'")->delete();

                if($request->eventFiles) {
                    foreach ($request->eventFiles as $array) {
                        $originalFichier= $array['file'];
                        $file_id = UUIDHelpers::getUUID().'.'.$originalFichier->getClientOriginalExtension();

                        Storage::disk('public')->put('uploads/'.$file_id, file_get_contents($originalFichier));

                        EventImage::create([
                            'event'=>$event->id,
                            'file_id'=> $file_id,
                            'file_name'=> Str::limit($originalFichier->getClientOriginalName(),'97','...'),
                        ]);
                    }
                }

                if($event->publier == true){
                    $job = (new SendInvitationEventJob("normal", null, $event->id));
                    dispatch($job);
                }

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
            $events = Event::whereIn('id', $arrayId)->get();

            foreach ($events as $event){
                if(!$event->delete()){
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
