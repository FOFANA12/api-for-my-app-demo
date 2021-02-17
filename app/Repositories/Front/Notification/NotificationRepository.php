<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Front\Notification;
use App\Models\NotificationsMessages;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationRepository
{
    public function getNotifications(Request $request)
    {

        $currentCustomerId = auth('customer')->user()->id;
        $searchTerm = $request->get('searchTerm') ?: null;
        $filter = $request->get('filter');

        $perPage = $request->get('perPage');

        if($filter == 'inbox'){
            $rows = DB::table('notifications_messages')
                ->join('customers','notifications_messages.notifiable_id','=','customers.id')
                ->select(
                    DB::raw("notifications_messages.id, type, notifiable_type, favoris, notifications_messages.created_at, notifications_messages.updated_at, notifications_messages.deleted_at, notifiable_id, data, read_at")
                )
                ->where(function ($query) use($searchTerm){
                    $query->whereRaw('LOWER(json_unquote(json_extract(data, "$.subject"))) like ?',['%' . mb_strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(json_unquote(json_extract(data, "$.body"))) like ?',['%' . mb_strtolower($searchTerm) . '%']);
                })
                ->whereNull('notifications_messages.deleted_at')
                ->where('customers.id','=',$currentCustomerId)
                ->orderBy('notifications_messages.created_at','desc')
                ->paginate($perPage);

        }else if($filter == 'favoris'){
            $rows = DB::table('notifications_messages')
                ->join('customers','notifications_messages.notifiable_id','=','customers.id')
                ->select(
                    DB::raw("notifications_messages.id, type, notifiable_type, favoris, notifications_messages.created_at, notifications_messages.updated_at, notifications_messages.deleted_at, notifiable_id, data, read_at")
                )
                ->where(function ($query) use($searchTerm){
                    $query->whereRaw('LOWER(json_unquote(json_extract(data, "$.subject"))) like ?',['%' . mb_strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(json_unquote(json_extract(data, "$.body"))) like ?',['%' . mb_strtolower($searchTerm) . '%']);
                })
                ->whereNull('notifications_messages.deleted_at')
                ->where('notifications_messages.favoris','=',true)
                ->where('customers.id','=',$currentCustomerId)
                ->orderBy('notifications_messages.created_at','desc')
                ->paginate($perPage);

        }else if($filter == 'trash'){
            $rows = DB::table('notifications_messages')
                ->join('customers','notifications_messages.notifiable_id','=','customers.id')
                ->select(
                    DB::raw("notifications_messages.id, type, notifiable_type, favoris, notifications_messages.created_at, notifications_messages.updated_at, notifications_messages.deleted_at, notifiable_id, data, read_at")
                )
                ->where(function ($query) use($searchTerm){
                    $query->whereRaw('LOWER(json_unquote(json_extract(data, "$.subject"))) like ?',['%' . mb_strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(json_unquote(json_extract(data, "$.body"))) like ?',['%' . mb_strtolower($searchTerm) . '%']);
                })
                ->whereNotNull('notifications_messages.deleted_at')
                ->where('customers.id','=',$currentCustomerId)
                ->orderBy('notifications_messages.created_at','desc')
                ->paginate($perPage);
        }

        return $rows;
    }

    public function getCounter(){
        $currentCustomerId = auth('customer')->user()->id;

        $counter = DB::table('notifications_messages')
            ->join('customers','notifications_messages.notifiable_id','=','customers.id')
            ->select(
                DB::raw('(SELECT COUNT(id) FROM notifications_messages WHERE read_at IS NULL AND notifiable_id = customers.id AND notifications_messages.deleted_at IS NULL) as unreadNotifications'),
                DB::raw('(SELECT COUNT(id) FROM notifications_messages WHERE favoris = true AND notifiable_id = customers.id AND notifications_messages.deleted_at IS NULL) as favorisNotifications'),
                DB::raw('(SELECT COUNT(id) FROM notifications_messages WHERE notifications_messages.deleted_at IS NOT NULL AND notifiable_id = customers.id) as trashedNotifications')
            )
            ->where('customers.id','=',$currentCustomerId)
            ->first();

        if(!$counter){
            $counter = [
                'unreadNotifications' => 0,
                'favorisNotifications' => 0,
                'trashedNotifications' => 0,
            ];
        }
        return $counter;
    }

    public function getNotification($id, $category)
    {

        if($category == 1){
            $notification = NotificationsMessages::where('id',$id)->first();
            $nextNotificationId = NotificationsMessages::where('created_at', '>', $notification->created_at)->first();
            $prevNotificationId = NotificationsMessages::where('created_at', '<', $notification->created_at)->first();
        }else if($category == 2){
            $notification = NotificationsMessages::where('id',$id)->first();
            $nextNotificationId = NotificationsMessages::where('created_at', '>', $notification->created_at)->first();
            $prevNotificationId = NotificationsMessages::where('created_at', '<', $notification->created_at)->first();
        }else if($category == 3){
            $notification = NotificationsMessages::withTrashed()->where('id',$id)->first();
            $nextNotificationId = NotificationsMessages::withTrashed()->where('created_at', '>', $notification->created_at)->first();
            $prevNotificationId = NotificationsMessages::withTrashed()->where('created_at', '<', $notification->created_at)->first();
        }

        return  ['notification' => $notification, 'next' => $nextNotificationId, 'prev' => $prevNotificationId];
    }

    public function getUnreadNotification()
    {
        $currentCustomerId = auth('customer')->user()->id;

        $notifications = DB::table('notifications_messages')
            ->join('customers','notifications_messages.notifiable_id','=','customers.id')
            ->select(
                DB::raw("notifications_messages.id, type, notifiable_type, favoris, notifications_messages.created_at, notifications_messages.updated_at, notifications_messages.deleted_at, notifiable_id, data, read_at")
            )
            ->whereNull('notifications_messages.deleted_at')
            ->whereNull('notifications_messages.read_at')
            ->where('customers.id','=',$currentCustomerId)
            ->orderBy('notifications_messages.created_at','desc')
            ->get();
        return  $notifications;
    }

    public function readNotification($id, $category)
    {
        if($category == 1){
            $notification = NotificationsMessages::where('id',$id)->first();
            $notification->read_at = Carbon::now();
            $notification->save();
        }else if($category == 2){
            $notification = NotificationsMessages::where('id',$id)->first();
            $notification->read_at = Carbon::now();
            $notification->save();

        }else if($category == 3){
            $notification = NotificationsMessages::withTrashed()->where('id',$id)->first();
            $notification->read_at = Carbon::now();
            $notification->save();
        }

        return  $notification->read_at;
    }

    public function setNotificationFavoris(Request $request)
    {
        DB::beginTransaction();
        try {
            $arrayId = $request->get('arrayId');
            $value = $request->get('value');

            foreach ($arrayId as $id){
                $notification = NotificationsMessages::where('id',$id)->first();
                $notification->favoris = $value;
                $notification->save();
            }

            DB::commit();

            $notifications = NotificationsMessages::whereIn('id', $arrayId)->get();
            return ['value' => $value, 'notifications' => $notifications];
        }catch (\Exception $e) {
            DB::rollback();
        }

        $notifications = NotificationsMessages::whereIn('id', $arrayId)->get();
        return ['value' => $value, 'notifications' => $notifications];
    }

    public function recoverInInbox(Request $request)
    {
        DB::beginTransaction();
        try {
            $arrayId = $request->get('arrayId');

            foreach ($arrayId as $id){
                $notification = NotificationsMessages::withTrashed()->where('id',$id)->first();
                $notification->deleted_at = null;
                $notification->save();
            }

            DB::commit();

            return true;
        }catch (\Exception $e) {
            Log::info($e->getMessage());
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
            $notifications = NotificationsMessages::whereIn('id', $arrayId)->get();

            foreach ($notifications as $notification){
                if(!$notification->delete()){
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
            DB::rollback();
        }

        return false;
    }

    public function destroyDefinitive($array)
    {
        $erreur = false;

        DB::beginTransaction();
        try {
            $arrayId = explode(",", $array);
            $notifications = NotificationsMessages::withTrashed()->whereIn('id', $arrayId)->get();
            foreach ($notifications as $notification){
                if(!$notification->forceDelete()){
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
            DB::rollback();
        }

        return false;
    }
}
