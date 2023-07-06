<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ToursListRequest;
use App\Http\Resources\TourResource;
use App\Models\Travel;

class TourController extends Controller
{
    public function index(Travel $travel, ToursListRequest $request)
    {
        $tours = $travel->tours()
            ->when($request->price_from, function ($q) use ($request) {
                $q->where('price', '>=', $request->price_from * 100);
            })
            ->when($request->price_to, function ($q) use ($request) {
                $q->where('price', '<=', $request->price_to * 100);
            })
            ->when($request->date_from, function ($q) use ($request) {
                $q->where('starting_date', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($q) use ($request) {
                $q->where('starting_date', '<=', $request->date_to);
            })
            ->when($request->sort_by && $request->sort_order, function ($q) use ($request) {
                $q->orderBy($request->sort_by, $request->sort_order);
            })
            ->orderBy('starting_date')->paginate();

        return TourResource::collection($tours);
    }
}
