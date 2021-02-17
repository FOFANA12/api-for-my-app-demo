<?php
/**
 * Created by PhpStorm.
 * User: D-XPERTS FOFANA
 * Date: 12/12/2020
 * Time: 11:08
 */

namespace App\Repositories\Back\Config;


use App\Http\Requests\Back\Config\GeneralRequest;
use Illuminate\Support\Facades\DB;
use Setting;
class GeneralRepository
{
    public function getConfig()
    {

        return [
            'app_name' => setting('app_name',config('app.name')),
            'app_developer' => setting('app_developer',config('app.editeur')),
            'app_version' => setting('app_version',config('app.version')),
            'app_time_zone' => setting('app_time_zone',config('app.timezone')),
            'app_currency' => setting('app_currency','MRU'),
            'work_time_start' => setting('work_time_start',''),
            'work_time_end' => setting('work_time_end',''),
            'timeZoneList' => \DateTimeZone::listIdentifiers(\DateTimeZone::ALL),
        ];

    }


    public function update(GeneralRequest $request)
    {
        DB::beginTransaction();
        try {

            Setting::set('app_name', $request->input('app_name') ? $request->input('app_name') : config('app.name'));
            Setting::set('app_developer', $request->input('app_developer') ? $request->input('app_developer') : config('app.editeur'));
            Setting::set('app_version', $request->input('app_version') ? $request->input('app_version') : config('app.version'));
            Setting::set('app_time_zone', $request->input('app_time_zone') ? $request->input('app_time_zone') : config('app.timezone'));
            Setting::set('app_currency', $request->input('app_currency'));
            Setting::set('work_time_start', $request->input('work_time_start'));
            Setting::set('work_time_end', $request->input('work_time_end'));

            DB::commit();

            return true;
        }catch (\Exception $e) {
            DB::rollback();
        }

        return false;
    }
}
