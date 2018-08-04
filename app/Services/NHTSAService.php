<?php

namespace App\Services;

use \GuzzleHttp\Client;
use Illuminate\Http\Request;

class NHTSAService
{
    protected $client;
    protected $api_url;

    /**
     * NHTSA Service constructor.
     * Sets API Service URL and HTTP Client.
     *
     * @return void
     */
    public function __construct()
    {
        $this->api_uri = env('NHTSA_URL');
        $this->client = new Client([
            'query'   => [
                'format' => 'json'
            ]
        ]);
    }

    /**
     * Get Vehicles from NHTSA API.
     *
     * @param Request   $request
     * @param integer   $modelYear
     * @param string    $manufacturer
     * @param string    $model
     *
     * @return array response
     */
    public function getVehicles(Request $request, $modelYear, $manufacturer, $model) : array
    {
        $withRating = $this->getWithRating($request);

        $endpointPath = 'modelyear/' . $modelYear . '/make/' . $manufacturer . '/model/' . $model;

        try {
            $vehicles = $this->requestData($endpointPath);
        } catch (\Exception $e) {
            return $this->emptyResponse();
        }

        return $this->setResponse($vehicles, $withRating);
    }

    /**
     * Request data from NHTSA API.
     *
     * @param string    $endpoint
     *
     * @return Json response
     */
    protected function requestData($endpoint)
    {
        $response = $this->client->request(
            'GET',
            $this->api_uri . $endpoint
        );

        if (json_decode($response->getBody()) === NULL) {
            throw new \Exception;
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Get withRating parameter from Request.
     *
     * @param Request   $request
     *
     * @return boolean
     */
    protected function getWithRating(Request $request) : bool
    {
        return  $request->has('withRating') && $request->get('withRating') === 'true';
    }

    /**
     * Set Response according to withRating parameter.
     *
     * @param array     $data
     * @param boolean   $withRating
     *
     * @return array response
     */
    protected function setResponse($data, $withRating) : array
    {
        if ($withRating) {
            return $this->withRatingResponse($data);
        }

        return $this->withOutRatingResponse($data);
    }

    /**
     * Get vehicle data with Rating value.
     *
     * @param array     $data
     *
     * @return array response
     */
    protected function withRatingResponse($data) : array
    {
        $vehicles = array_map(function($vehicle) {
            return [
                'CrashRating' => $this->getCrashRating($vehicle['VehicleId']),
                'Description' => $vehicle['VehicleDescription'],
                'VehicleId'   => $vehicle['VehicleId']
            ];
        }, $data['Results']);

        return   [
            'Count'     =>  $data['Count'],
            'Results'   =>  $vehicles
        ];
    }

    /**
     * Get vehicle data.
     *
     * @param array     $data
     *
     * @return array response
     */
    protected function withOutRatingResponse($data) : array
    {
        $vehicles = array_map(function($vehicle) {
            return [
                'Description' => $vehicle['VehicleDescription'],
                'VehicleId' => $vehicle['VehicleId']
            ];
        }, $data['Results']);

        return  [
            'Count'     =>  $data['Count'],
            'Results'   =>  $vehicles
        ];
    }

    /**
     * Get vehicle's Crash Rating value.
     *
     * @param integer   $vehicleId
     *
     * @return string
     */
    protected function getCrashRating($vehicleId) : string
    {
        $endpointPath = 'VehicleId/' . $vehicleId;
        $rating = $this->requestData($endpointPath);

        return $rating['Results'][0]['OverallRating'];
    }

    /**
     * Get vehicle data with Rating value.
     *
     * @return array
     */
    protected function emptyResponse() : array
    {
        return [
            'Count'     =>  0,
            'Results'   =>  []
        ];
    }
}
