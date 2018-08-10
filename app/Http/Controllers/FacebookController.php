<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class FacebookController extends Controller
{
    public function login(Request $request){

        $appID = env('FACEBOOK_APP_ID','1053174974861205');

        $url = 'https://www.facebook.com/v3.1/dialog/oauth?client_id='.$appID.'&redirect_uri=https://localhost/token/&response_type=token';

        return \Redirect::to($url);
    }

    public function callback(Request $request){
        return view('fbcallback');
    }

    public function saveAccessToken(Request $request){

        if($request->access_token==null){
            return response()->json([
                'response' => 'error'
            ],500); 
        }

        session(['fb_access_token' => $request->access_token]);

        return response()->json([
            'response' => 'ok'
        ],200); 

    }

    public function logout(Request $request){
        \Session::forget('fb_access_token');
    }




}