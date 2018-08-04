<?php

namespace App\Http\Controllers;

use App\Services\NHTSAService;
use Illuminate\Http\Request;

class VehiclesController extends Controller
{
    protected $service;

    /**
     * Vehicle Controller constructor.
     * Sets Vehicle data service.
     *
     * @param NHTSAService  $service
     *
     * @return void
     */
    public function __construct(NHTSAService $service)
    {
        $this->service = $service;
    }

    /**
     * Get Vehicles from NHTSA Service (HTTP GET)
     *
     * @param Request   $request
     * @param integer   $modelYear
     * @param string    $manufacturer
     * @param string    $model
     *
     * @return Json response
     */
    public function getVehicles(Request $request, $modelYear = null, $manufacturer = null, $model = null)
    {
        return response()->json($this->service->getVehicles($request, $modelYear, $manufacturer, $model));
    }

    /**
     * Get Vehicles from NHTSA Service (HTTP POST)
     *
     * @param Request   $request
     *
     * @return Json response
     */
    public function postVehicles(Request $request)
    {
        $manufacturer = $request->json()->get('manufacturer');
        $model = $request->json()->get('model');
        $modelYear = $request->json()->get('modelYear');

        return response()->json($this->service->getVehicles($request, $modelYear, $manufacturer, $model));
    }
}
