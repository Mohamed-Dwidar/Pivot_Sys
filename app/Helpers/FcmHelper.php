<?php

namespace App\Helpers;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

use LaravelFCM\Facades\FCM;

class FcmHelper
{
      public static function sendNotification($targets, $device_type = 'all', $notification_title = '', $notification_body = '', $notifi_data = array())
      {
            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 60 * 24 * 7);

            $notificationBuilder = new PayloadNotificationBuilder($notification_title);
            $notificationBuilder->setBody($notification_body)
                  ->setSound('default');


            $dataBuilder = new PayloadDataBuilder();

            $notifi_data['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
            $dataBuilder->addData($notifi_data);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();


            $downstreamResponse = FCM::sendTo($targets, $option, $notification, $data);
            // dd($downstreamResponse);
            return [
                  'numberSuccess' => $downstreamResponse->numberSuccess(),
                  'numberFailure' => $downstreamResponse->numberFailure(),
                  'numberModification' => $downstreamResponse->numberModification(),
                  'tokensToDelete' => $downstreamResponse->tokensToDelete(),              // return Array - you must remove all this tokens in your database
                  'tokensToModify' => $downstreamResponse->tokensToModify(),              // return Array (key : oldToken, value : new token - you must change the token in your database)
                  'tokensToRetry' => $downstreamResponse->tokensToRetry(),                // return Array - you should try to resend the message to the tokens in the array
                  'tokensWithError' => $downstreamResponse->tokensWithError()             // return Array (key:token, value:error) - in production you should remove from your database the tokens
            ];
      }
      
}
