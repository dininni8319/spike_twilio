<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

class TwilioController extends Controller
{
    // find the your account SID and Auth at twilio.com/console
    // and set the envirement variables. Sie at http://twilio.io/secure

    //Account SID e' in https://console.twilio.com/us1/account/keys-credentials/api-keys?frameUrl=%2Fconsole%2Fproject%2Fapi-keys%2Fcreate%3Fregion%3Dus1%26x-target-region%3Dus1
    public function newRoom(){
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        
        //create a new client twillio
        $twilio = new Client($sid, $token);

        $room_name = "rehacktor_room_name". rand(1, 100000000); // deve cambiare per ogni STREAMER!!

        $room = $twilio->video->v1->rooms->create(['uniqueName' => $room_name]);

        $indentify = "Streamer";   //Todo deve cambiare per ogni stremer!! es. id + name; 

        $userSid = getenv('TWILIO_USER_SID');

        // Create an access token, which will serialize and send to the client
        $token = new AccessToken(

            $userSid,   // USERSID
            $sid,      // API SID
            $token,    // SECRET
            3600, $indentify
        );
        
        // Create your video grant
        $videoGrant = new VideoGrant();
        $videoGrant->setRoom(($room_name));

        //Add grant token
        $token->addGrant($videoGrant);

        return view('newroom', [
            "room_sid" => $room->sid,
            "room_name" => $room_name,
            "jwt" => $token->toJWT()
        ]);
    }
}
