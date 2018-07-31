<?php

namespace App\Http\Controllers;

use App\Bbq;
use App\BbqUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarbecueController extends Controller
{
    /**
     * Display a listing of barbecues.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $minDist = 10;
        $bbqs = Bbq::all()->filter(function ($barbecue, $key) use ($user, $minDist) {
            if($user->id == $barbecue->user_id || !isset($user->last_latitude) || !isset($user->last_longitude) || !isset($barbecue->latitude) || !isset($barbecue->longitude))
                return false;
            return self::distance($barbecue->latitude, $barbecue->longitude, $user->last_latitude, $user->last_longitude, $minDist);
        })->values();
        return response()->json([
            'barbecues' => $bbqs
        ], 200);
    }

    /**
     * Store a newly created barbecue in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required|string',
            'description' => 'string|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'model' => 'string|max:20',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'_'.$user->id.'.'.$image->getClientOriginalExtension();
            $destinationPath = 'public/uploads/barbecues';
            $imagePath = $destinationPath. "/".  $name;
            $image->storeAs($destinationPath, $name);
            $request->image = $name;
        }
        
        $bbq = new Bbq([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'model' => $request->model,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        $bbq->save();
        return response()->json([
            'message' => 'Successfully created barbecue!',
            'barbecue' => $bbq,
        ], 201);
    }

    /**
     * Display the specified barbecue.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $bbq = Bbq::whereId($id)->first();
        return response()->json([
            'barbecue' => $bbq,
        ], 200);
    }

    /**
     * Update the specified barbecue in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $bbq = Bbq::whereId($id)->first();
        if($request->user()->id !== $bbq->user_id)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $request->validate([
            'name' => 'required|string',
            'description' => 'string|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'model' => 'string|max:20',
        ]);
        $hasImage = $request->hasFile('image');
        $destinationPath = 'public/uploads/barbecues';
        $oldImageName = str_replace('/storage/uploads/barbecues/', '', $bbq->image);
        if ($hasImage) {
            $oldImage = Storage::delete($destinationPath . '/' . $oldImageName);
            $image = $request->file('image');
            $name = time().'_'.$bbq->user_id.'.'.$image->getClientOriginalExtension();
            $image->storeAs($destinationPath, $name);
            $request->image = $name;
        }

        $bbq->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $hasImage ? $request->image : $oldImageName,
            'model' => $request->model,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        return response()->json([
            'message' => 'Successfully updated barbecue!',
            'barbecue' => $bbq,
        ], 201);
    }

    /**
     * Remove the specified barbecue from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $bbq = Bbq::whereId($id)->first();
        if($request->user()->id !== $bbq->user_id)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        Storage::delete('public/uploads/barbecues/' . $bbq->image);
        $bbq->delete();
        return response()->json([
            'message' => 'Successfully deleted barbecue!',
            'barbecue' => $bbq,
        ], 204);
    }

    /**
     * Calculate distance between 2 coords.
     *
     * @return double
     */
    public function distance($lat1, $lon1, $lat2, $lon2, $minDist = 10)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.609344;

        return $kilometers <= $minDist;
    }

}
