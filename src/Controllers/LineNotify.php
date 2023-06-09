<?php

namespace Gcreate\LineNotify\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Arr;
use Illuminate\Support\Facades\Http;

class LineNotify extends Controller
{
        
    const API_URL = 'https://notify-api.line.me/api/';

    const OAUTH_URL = 'https://notify-bot.line.me/oauth/';

    
    public function send($user, $text, $images = []) {

        $body = [
            "message" => "\n{$text}",
        ];

        if(!empty($images)){
            $body = array_merge($body, $images);
        }
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$user->line_notify_access_token}"
        ]);

        // $imageUrl = null;
        // if( isset($args['imageFile']) && !empty($args['imageFile']) ){
        //     $imageUrl = $args['imageFile'];
        //     unset($args['imageFile']);
        // }


        // if( $imageUrl ){
        //     $response = $response->attach('imageFile', file_get_contents($imageUrl), basename($imageUrl), [
        //         'Content-Type' => 'image/png'
        //     ]);
        // }

        $response = $response->asForm()->post(self::API_URL.'notify', $body);

        if( $response->ok() ){
            return true;
        }else{
            return $response->body();
        }
    }
    

    public function bindLineUrl($user)
    {
        $state = uniqid();
        $user->line_notify_state = $state;
        $user->save();

        return self::OAUTH_URL.'authorize?' . Arr::query([
            'response_type' => 'code',
            'scope' => 'notify',
            'response_mode' => 'form_post',
            'client_id' => config('line-notify.client_id'),
            'redirect_uri' => route('line-notify.callback'),
            'state' => $state
        ]);
    }

    public function callback($request, $user)
    {
        $state = $request->state;

        if( !$user->line_notify_state || $user->line_notify_state != $state ){
            return false;
        }

        $response = Http::asForm()->post(self::OAUTH_URL.'token', [
            'grant_type' => "authorization_code",
            'redirect_uri' => route('line-notify.callback'),
            'client_id' => config('line-notify.client_id'),
            'client_secret' => config('line-notify.client_secret'),
            'code' => $request->code
        ]);
            
        $access_token = $response->json('access_token');
        $user->line_notify_access_token = $access_token;
        $user->line_notify_state = '';
        $user->save();

        return $response->ok() ? true : false;
    }

    public function revoke($user){

        if( !$user->line_notify_access_token ){
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$user->line_notify_access_token}"
        ])->post(self::API_URL.'revoke');
        
        $user->line_notify_access_token = '';
        $user->save();

        return  $response->ok() ? true : false;
    }

    public function check_token($token=''){

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->get(self::API_URL.'status');

        return ( $response->ok() ) ? true : false ;
    }

}