<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Front\Event;
use App\Helpers\ConfigApp;
use App\Models\EventInvitation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventRepository
{
    public function getPublished(Request $request)
    {
        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;
        $currentCustomerId = auth('customer')->user()->id;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $date_format = ConfigApp::date_format_sql();
        $heure_format = ConfigApp::heure_format_sql();

        $rows = DB::table('events')
            ->select(
                DB::raw("events.id, events.nom, events.date, events.prix, events.max_invite, description, publier"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.participation = 1 AND event_invitations.approbation = 1) as nbre_invite_confirm"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as am_invite")
            )
            ->where(function ($query) use($searchTerm, $date_format, $heure_format){
                $query->whereRaw('LOWER(events.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhere(DB::raw("DATE_FORMAT(events.date, '$date_format $heure_format')"), 'like', '%' . $searchTerm . '%');
            })
            ->whereNull('events.deleted_at')
            ->where('publier','=',true)
            //->where(DB::raw("DATE_FORMAT(events.date, '%Y-%m-%d %H:%i')"),'>=',Carbon::now()->format('Y-m-d H:i'))
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    public function getMyEvents(Request $request)
    {
        $currentCustomerId = auth('customer')->user()->id;

        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $date_format = ConfigApp::date_format_sql();
        $heure_format = ConfigApp::heure_format_sql();

        $rows = DB::table('events')
            ->join('event_invitations','events.id','=','event_invitations.event')
            ->select(
                DB::raw("events.id, events.nom, events.date, events.prix, events.max_invite, description, publier"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.participation = 1 AND event_invitations.approbation = 1) as nbre_invite_confirm"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as am_invite"),
                DB::raw("(SELECT created_at FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as invitation_date"),
                DB::raw("(SELECT participation FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as participation_reponse"),
                DB::raw("(SELECT approbation FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as approbation_reponse")
            )
            ->where(function ($query) use($searchTerm, $date_format, $heure_format){
                $query->whereRaw('LOWER(events.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhere(DB::raw("DATE_FORMAT(events.date, '$date_format $heure_format')"), 'like', '%' . $searchTerm . '%');
            })
            ->whereNull('events.deleted_at')
            ->where('publier','=',true)
            ->where('event_invitations.customer','=',$currentCustomerId)
            ->where('event_invitations.participation','=',1)
            ->where('event_invitations.approbation','=',1)
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    public function getMyInvitations(Request $request)
    {
        $currentCustomerId = auth('customer')->user()->id;

        $sort = $request->get('sort') ? json_decode($request->get('sort')) : [];
        $searchTerm = $request->get('searchTerm') ?: null;

        $sortType = $sort->type ?: '';
        $sortColumn = $sort->field ?: '';

        $perPage = $request->get('perPage');

        $date_format = ConfigApp::date_format_sql();
        $heure_format = ConfigApp::heure_format_sql();

        $rows = DB::table('events')
            ->join('event_invitations','events.id','=','event_invitations.event')
            ->select(
                DB::raw("events.id, events.nom, events.date, events.prix, events.max_invite, description, publier"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.participation = 1 AND event_invitations.approbation = 1) as nbre_invite_confirm"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as am_invite"),
                DB::raw("(SELECT created_at FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as invitation_date"),
                DB::raw("(SELECT participation FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as participation_reponse"),
                DB::raw("(SELECT approbation FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as approbation_reponse")
            )
            ->where(function ($query) use($searchTerm, $date_format, $heure_format){
                $query->whereRaw('LOWER(events.nom) like ? ', ['%' . mb_strtolower($searchTerm) . '%'])
                    ->orWhere(DB::raw("DATE_FORMAT(events.date, '$date_format $heure_format')"), 'like', '%' . $searchTerm . '%');
            })
            ->whereNull('events.deleted_at')
            ->where('publier','=',true)
            ->where('event_invitations.customer','=',$currentCustomerId)
            ->orderBy($sortColumn,$sortType)
            ->paginate($perPage);

        return $rows;
    }

    public function setActionEvent(Request $request){
        $eventId = $request->get('event');
        $value = $request->get('value');
        $customerId = $request->get('customerId');

        DB::beginTransaction();
        try {
            if($value == 1){//confirmation
                $invitation = EventInvitation::where('event',$eventId)->where('customer',$customerId)->first();
                if($invitation->participation_date) return false;

                $invitation->participation = 1;
                $invitation->participation_date = Carbon::now();;
                $invitation->save();
            }else if($value == 0){//refus
                $invitation = EventInvitation::where('event',$eventId)->where('customer',$customerId)->first();
                if($invitation->participation_date) return false;

                $invitation->participation = 0;
                $invitation->participation_date = Carbon::now();
                $invitation->save();
            }


            DB::commit();

            return true;
        }catch (\Exception $e) {
            Log::info($e->getMessage());
            DB::rollback();
        }
        return false;
    }


    public function getEvent($id)
    {
        $currentCustomerId = auth('customer')->user()->id;

        $event = DB::table('events')
            ->select(
                DB::raw("events.id, events.nom, events.date, events.prix, events.max_invite, description, publier, events.publication_date"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.participation = 1 AND event_invitations.approbation = 1) as nbre_invite_confirm"),
                DB::raw("(SELECT COUNT(id) FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as am_invite"),
                DB::raw("(SELECT created_at FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as invitation_date"),
                DB::raw("(SELECT participation_date FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as participation_date"),
                DB::raw("(SELECT approbation_date FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as approbation_date"),
                DB::raw("(SELECT participation FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as participation_reponse"),
                DB::raw("(SELECT approbation FROM event_invitations WHERE event = events.id AND event_invitations.deleted_at IS NULL AND event_invitations.customer = '$currentCustomerId') as approbation_reponse")
            )
            ->whereNull('events.deleted_at')
            ->where('events.id','=',$id)
            ->first();

        return ['event' => $event];
    }
}
