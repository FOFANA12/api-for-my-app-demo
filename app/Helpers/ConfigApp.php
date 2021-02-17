<?php
/**
 * Created by PhpStorm.
 * User: TOSHIBA
 * Date: 03/08/2019
 * Time: 18:36
 */

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConfigApp
{
    public static function getPlanningColors(){
        return ['reservation' => '#1087a7', 'inactive' => '#EA5455'];
    }

    public static function moneyFormat($valeur) {
        return $valeur = number_format($valeur,2, '.', ' ');
    }

    public static function date_format_php(){
        /*switch (setting('dateFormat',config('app.date'))){
            case 'mm/dd/yyyy':
                $format = 'm/d/Y';
            break;
            default://dd/mm/yyyy
                $format = 'd/m/Y';
            break;
        }*/

        $format = 'd/m/Y';
        return $format;
    }

    public static function date_format_sql(){
        /*switch (setting('dateFormat',config('app.date'))){
            case 'mm/dd/yyyy':
                $format = '%m/%d/%Y';
                break;
            default://dd/mm/yyyy
                $format = '%d/%m/%Y';
            break;
        }*/

        $format = '%d/%m/%Y';
        return $format;
    }

    public static function heure_format_sql(){
        /*switch (setting('heureFormat',config('app.heure'))){
            case 'h:i':
                $format = '%h:%i';
                break;
            default://H:i
                $format = '%H:%i';
                break;
        }*/
        $format = '%H:%i';
        return $format;
    }

    public static function heure_format_php(){
        /*switch (setting('heureFormat',config('app.heure'))){
            case 'h:i':
                $format = 'h:i';
                break;
            default://H:i
                $format = 'H:i';
                break;
        }*/

        $format = 'H:i';
        return $format;
    }

    public static function getRef($table){

        $id = DB::select("SHOW TABLE STATUS LIKE '$table'");
        $id = $id[0]->Auto_increment;
        $datenow = Carbon::now();

        switch ($table){
            case"commande_repas":
                $ref = 'ORD-';
                if($id<10) $ref .= Carbon::parse($datenow)->format('m-Y').'-00'.$id;
                else if($id<100) $ref .= Carbon::parse($datenow)->format('m-Y').'-0'.$id;
                else $ref .= Carbon::parse($datenow)->format('m-Y').'-'.$id;
            break;
        }
        return $ref;
    }


}
