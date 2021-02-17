<?php

namespace App\Http\Controllers\Front\Repas;

use App\Repositories\Front\Repas\RestaurantRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    private $restaurantRepository;

    public function __construct(RestaurantRepository $restaurantRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
    }


    public function getRestaurants()
    {
        return response()->json(['restaurants' =>  $this->restaurantRepository->getRestaurants()]);
    }

    public function getMenus(Request $request){
        return response()->json($this->restaurantRepository->getMenus($request));
    }
}
