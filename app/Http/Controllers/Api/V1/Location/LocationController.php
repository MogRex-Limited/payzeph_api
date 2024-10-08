<?php

namespace App\Http\Controllers\Api\V1\Location;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Location\CityResource;
use App\Http\Resources\Location\StateResource;
use App\Http\Resources\Location\TownResource;
use App\Models\City;
use App\Models\Lga;
use App\Models\State;
use App\Models\Town;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function states(Request $request)
    {
        try {
            $builder = State::with("country");
            if (!empty($key = $request->country_id)) {
                $builder = $builder->where("country_id", $key);
            }
            $states = $builder->get();
            $data = StateResource::collection($states);
            return ApiHelper::validResponse("States returned successfully", $data);
        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function cities(Request $request)
    {
        try {
            $builder = City::with(["state", "state.country"]);

            if (!empty($key = $request->country_id)) {
                $builder = $builder->whereHas("state.country", function ($country) use ($key) {
                    $country->where("id", $key);
                });
            }
            if (!empty($key = $request->state_id)) {
                $builder = $builder->where("state_id", $key);
            }

            if (!empty($key = $request->search)) {
                $builder = $builder->search($key);
            }

            $cities = $builder->get();
            $data = CityResource::collection($cities);
            return ApiHelper::validResponse("Cities returned successfully", $data);
        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function lgas(Request $request)
    {
        try {
            $builder = Lga::with(["state", "country"]);

            if (!empty($key = $request->country_id)) {
                $builder = $builder->where("country_id", $key);
            }

            if (!empty($key = $request->state_id)) {
                $builder = $builder->where("state_id", $key);
            }

            if (!empty($key = $request->search)) {
                $builder = $builder->search($key);
            }

            $cities = $builder->get();
            $data = CityResource::collection($cities);
            return ApiHelper::validResponse("Cities returned successfully", $data);
        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function towns(Request $request)
    {
        try {
            $builder = Town::with(["state", "country"]);

            if (!empty($key = $request->search)) {
                $builder = $builder->search($key);
            }

            if (!empty($key = $request->country_id)) {
                $builder = $builder->where("country_id", $key);
            }

            if (!empty($key = $request->state_id)) {
                $builder = $builder->where("state_id", $key);
            }

            if (!empty($key = $request->lga_id)) {
                $builder = $builder->where("lga_id", $key);
            }

            $towns = $builder->latest()->get();
            $data = TownResource::collection($towns);
            return ApiHelper::validResponse("Towns returned successfully", $data);
        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }
}
