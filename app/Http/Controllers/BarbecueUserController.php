<?php

namespace App\Http\Controllers;

use App\Bbq;
use App\BbqUser;
use Illuminate\Http\Request;

class BarbecueUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $reservedBarbecues = BbqUser::whereUserId($userId)->get();
        return response()->json([
            'reservedBarbecues' => $reservedBarbecues
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $reservedBarbecue = BbqUser::whereId($id)->first();
        $userId = $request->user()->id;
        if($userId !== $reservedBarbecue->user_id)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        return response()->json([
            'reservedBarbecue' => $reservedBarbecue,
        ], 200);
    }

    /**
     * reserve a barbecue by a user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'reservedFrom' => 'required|date_format:"Y-m-d H:i:s"|after:'.date("Y-m-d H:i:s"),
            'reservedTo' => 'required|date_format:"Y-m-d H:i:s"|after:'.$request->reservedFrom,
        ]);
        $bbq = Bbq::whereId($request->bbqId)->first();
        if(is_null($bbq))
            return response()->json([
                'message' => 'Barbecue not found.'
            ], 404);
        // dd();
        $reservedBarbecues = BbqUser::whereBbqId($bbq->id)
        ->whereBetween('reserved_from', [$request->reservedFrom, $request->reservedTo])
        ->orWhereBetween('reserved_to', [$request->reservedFrom, $request->reservedTo])
        ->count();
        if($reservedBarbecues > 0)
            return response()->json([
                'message' => 'The selected date is already reserved. Try selecting another date.'
            ], 400);
        $reservedBarbecue = new BbqUser([
            'user_id' => $user->id,
            'bbq_id' => $request->bbqId,
            'reserved_from' => $request->reservedFrom,
            'reserved_to' => $request->reservedTo,
        ]);
        $reservedBarbecue->save();
        return response()->json([
            'message' => 'Successfully reserved barbecue!',
            'reservedBarbecue' => $reservedBarbecue,
        ], 201);
    }

    /**
     * update a reserved barbecue by a user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id (BarbecueUser ID)
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $bbqUser = BbqUser::whereId($id)->first();
        if(is_null($bbqUser))
            return response()->json([
                'message' => 'Reserved barbecue not found.'
            ], 404);
        if($request->user()->id !== $bbqUser->user_id)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $request->validate([
            'reservedFrom' => 'required|date_format:"Y-m-d H:i:s"|after:'.date("Y-m-d H:i:s"),
            'reservedTo' => 'required|date_format:"Y-m-d H:i:s"|after:'.$request->reservedFrom,
        ]);
        $reservedBarbecues = BbqUser::whereBbqId($id)->where('id', '<>', $id)
        ->whereBetween('reserved_from', [$request->reservedFrom, $request->reservedTo])
        ->orWhereBetween('reserved_to', [$request->reservedFrom, $request->reservedTo])
        ->count();
        if($reservedBarbecues > 0)
            return response()->json([
                'message' => 'The selected date is already reserved. Try selecting another date.'
            ], 400);
        $bbqUser->update([
            'reserved_from' => $request->reservedFrom,
            'reserved_to' => $request->reservedTo,
        ]);
        return response()->json([
            'message' => 'Successfully updated reserved barbecue!',
            'reservedBarbecue' => $bbqUser,
        ], 201);
    }

    /**
     * delete a reserved barbecue by a user from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $bbqUser = BbqUser::whereId($id)->first();
        if($request->user()->id !== $bbqUser->user_id)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $bbqUser->delete();
        return response()->json([
            'message' => 'Successfully deleted reserved barbecue!',
            'reservedBarbecue' => $bbqUser,
        ], 204);
    }
}
