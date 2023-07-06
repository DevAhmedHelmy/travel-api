<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest;
use App\Http\Resources\TravelResource;
use App\Models\Travel;

/**
 * @group Admin endpoints
 */
class TravelController extends Controller
{
    /**
     * POST Travel
     *
     * @authenticated
     */
    public function store(TravelRequest $request)
    {

        $travel = Travel::create($request->validated());

        return new TravelResource($travel);
    }

    /**
     * PUT Travel
     *
     * @authenticated
     */
    public function update(Travel $travel, TravelRequest $request)
    {
        $travel->update($request->validated());

        return new TravelResource($travel);
    }
}
