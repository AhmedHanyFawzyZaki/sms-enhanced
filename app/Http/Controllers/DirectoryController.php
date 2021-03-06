<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TargetNumber;
use App\User;
use App\Http\Requests;
use App\Http\Session;
use App\Http\Controllers\Controller;
use Twilio\Twiml;
/**
 * use the following to send sms
 */
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Log;

class DirectoryController extends Controller {

    /**
     * API Coming from another app
     * @param Request $request
     */
    public function saveSmsLog(Request $request) {
        $body = $request->input('Body');
        $from = $request->input('From');
        $to = $request->input('To');
        $reply = $request->input('Reply');

        $model = new \App\SmsLog();
        $model->sent_from = $from;
        $model->sent_to = $to;
        $model->message = $body;
        $model->reply = $reply;

        $model->save();
    }

    public function incomingSmsHandling(Request $request) {
        $msg_arr = [];
        $is_suspended = 0;
        $body = $request->input('Body');
        $from = $request->input('From');
        $to = $request->input('To');
        if ($body) {
            $msg_arr = explode(' ', $body);
            $is_suspended = $this->_checkForSuspensionText($msg_arr);
        }

        $target = TargetNumber::where('target_number', $from)->first();
        if ($target) {
            $target->is_suspended = $is_suspended;
            $target->save();
        } else {
            $target = new TargetNumber();
            $target->target_number = $from;
            $target->is_suspended = $is_suspended;
            $target->save();
        }

        $this->_insertNewIncomingSms($from, $to, $body);
        $this->_receiveAcknowledgment($from, $to, $body);
    }

    private function _checkForSuspensionText($arr) {
        $is_suspended = 0;
        if ($arr) {
            foreach ($arr as $v) {
                $keyword = strtolower($v);
                if ($keyword == 'stop' || $keyword == '"stop"') {
                    $is_suspended = 1;
                }
            }
        }
        return $is_suspended;
    }

    private function _insertNewIncomingSms($from, $to, $message) {
        $model = new \App\InOutBoundSms();
        $model->sent_from = $from;
        $model->sent_to = $to;
        $model->message = $message;
        $model->is_outbound = 0;
        $model->is_processed = 1; //all incoming sms should be marked as processed
        $model->save();
    }

    private function _receiveAcknowledgment($from, $to, $message) {
        /**
         * Email
         */
		$admin = User::where('id', '1')->first();
		$targetEmail = $admin->target_email ? $admin->target_email : config('TARGET_EMAIL');
        $targetSubject = 'New incoming sms has been received from (' . $from . ')';
        $targetMessage = 'From: ' . $from . ' <br />'
                . 'To: ' . $to . ' <br />'
                . 'Message: "' . $message . '"';
        $headers = 'From: ' . env('MAIL_ADDRESS', 'twilio@nbob.org') . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        mail($targetEmail, $targetSubject, $targetMessage, $headers);

        /**
         * SMS
         */
        $targetPhone = $admin->target_phone ? $admin->target_phone : config('TARGET_PHONE');
        $this->_sendSMS($targetPhone, $targetMessage);
    }

    /**
     * send sms using twilio account configured in the env file
     * @param type $from
     * @param type $to
     * @param type $message
     */
    private function _sendSMS($to, $message) {
        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $twilioNumber = env('APP_NUMBER', '+15614752885');

        $client = new Client($accountSid, $authToken);

        try {
            $client->messages->create(
                    $to, [
                "body" => $message,
                "from" => $twilioNumber
                    //   On US phone numbers, you could send an image as well!
                    //  'mediaUrl' => $imageUrl
                    ]
            );
            Log::info('Message sent to ' . $to);
            return '';
        } catch (TwilioException $e) {
            return $e;
            /* echo $e;
              die; */
        }
    }
}
