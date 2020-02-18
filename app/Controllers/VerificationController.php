<?php

namespace App\Controllers;

use App\Views\View;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\EmailVerification;
use App\Models\PhoneNumberVerification;

class VerificationController extends Controller
{
    const EMAIL_SALT = '_cic_email_salt_grulog';
    const PHONE_NUMBER_SALT = '_cic_phone_number_salt_grulog';

    protected $view;
    protected $defaultCodeLength = 4;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function phoneNumber(Request $request, Response $response)
    {
        $phone = $request->getParam('phone');

        $code = $this->generatePhoneNumberSaltCode();

        // $result = savePhoneNumberCode(['phoneNumber' => $phone, 'code' => $code['complete']]);
        $phoneVerification = PhoneNumberVerification::create([
            'phone_number' => $phone,
            'code' => $code['complete'],
        ]);


        if($phoneVerification) {
            // Call method to send sms at the user
            if($this->sendSMS($phone, 'Code de validation CIC : '.$code['short']))
                return $response->withJson(['success' => true, 'message' => 'Le code de vérification a été envoyé']);
            else
                return $response->withJson(['success' => false, 'message' => 'Le code de vérification n\'a pas pu être envoyé']);
        }
        else echo json_encode(['success' => false]);
    }

    public function email(Request $request, Response $response)
    {
        return $response->withJson(['success' => true]);
    }

    public function generateEmailSaltCode($length = null)
    {
        if(!$length) $length = $this->defaultCodeLength;
        $result = '';
        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        return ['complete' => sha1($result.self::EMAIL_SALT), 'short' => $result];
    }

    public function generatePhoneNumberSaltCode($length = null)
    {
        if(!$length) $length = $this->defaultCodeLength;
        $result = '';
        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        return ['complete' => sha1($result.self::PHONE_NUMBER_SALT), 'short' => $result];
    }

    public function sendSMS($phoneNumber, $smsBody)
    {
        $title = 'INFO';

        // Check params valid format
        if( (!$phoneNumber) || (strlen($phoneNumber) != 10) || (!$smsBody) ) return false;
    
        $result = null;
        $ApiSmsConnectBaseUrl = 'http://www.sms.ma/mcms/sendsms/';
        $login = 'creditinfo';
        $password = 'Cd58-Aqm91';
    
        // Phone number format
        if($phoneNumber[0] == '0') $phoneNumber = substr_replace($phoneNumber, '+212', 0, 1);
        else $phoneNumber = '+212'.$phoneNumber;
    
        $completeApiUrl = $ApiSmsConnectBaseUrl.'?'.'login='.$login.'&password='.$password.'&oadc='.rawurlencode($title).'&msisdn_to='.$phoneNumber.'&body='.rawurlencode($smsBody);
    
        // Use SMS Connect API to send sms at one phone number
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => $completeApiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
    
        $response = curl_exec($curl);
    
        curl_close($curl);
    
        // Convert xml to json and interpret response
        $xml = new \SimpleXMLElement($response);
        $json = json_encode($xml);
        $response = json_decode($json, true);
        
        if($response['statuscode'] == '0') $result = true;
    
        return $result;
    }
   
}
