<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] lastName
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] zipCode
     * @param  [string] lastLatitude
     * @param  [string] lastLongitude
     * @return [string] message
     */
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
            'zipCode' => 'required|string|min:4',
        ]);
        $user = new User([
            'name' => $request->name,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'zip_code' => $request->zipCode,
            'last_latitude' => $request->lastLatitude,
            'last_longitude' => $request->lastLongitude,
        ]);
        $user->save();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();
        $user = $user->whereId($user->id)->with(['reserves', 'barbecues'])->first();
        return response()->json([
            'message' => 'Successfully created user!',
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @return [string] access_token
     * @return [string] token_type
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();
        $userId = $user->id;
        if(isset($request->lastLatitude) && isset($request->lastLongitude))
            $user->update([
                'last_latitude' => $request->lastLatitude,
                'last_longitude' => $request->lastLongitude
                ]);
        $user = $user->whereId($userId)->with(['reserves', 'barbecues'])->first();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $user = $user->whereId($userId)->with(['reserves', 'barbecues'])->first();
        return response()->json($user);
    }
}
