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

        // Get phone number in database
        $phoneNumberVerification = PhoneNumberVerification::where('phone_number', $phone)->first();

        // If phonenumber in db
        if($phoneNumberVerification) {
            // Number phone already in db
            if(!$phoneNumberVerification->phone_number_verified_at) return $response->withJson(['success' => true, 'message' => 'Entrez votre code dé vérification']);
            else return $response->withJson(['success' => false, 'message' => 'Ce numéro a déjà été utilisé']);
        } else {
            // Number phone not in db
            // Generate code and send code by SMS
            $code = $this->generatePhoneNumberSaltCode();

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
            else 
                return $response->withJson(['success' => false, 'message' => 'Le code de vérification n\'a pas pu être envoyé']);
        }
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

    protected function sendSMS($phoneNumber, $smsBody)
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

    protected function checkIfPhoneNumberCodeMatch(Request $request)
    {
        $status = false;
        $message = '';
        $phoneNumber = $request->getParam('phoneNumber');
        $code = $request->getParam('code');
        $phoneNumberVerification = PhoneNumberVerification::where('phone_number', $phoneNumber)->first();

        // If phone number present in phone_number_verifications table - Database 
        if($phoneNumberVerification) {
            if($phoneNumberVerification->phone_number_verified_at) $message = 'Ce numero a déjà été validé';
            else {
                if( sha1($code.self::PHONE_NUMBER_SALT) == $phoneNumberVerification->code){
                    $phoneNumberVerification->update(['phone_number_verified_at' => time()]);
                    $status = true;
                    $message = 'Votre numéro de téléphone a été vérifié avec succès';
                } else {
                    $message = 'Le code saisi est erroné';
                }
            }
        } else {
            $message = 'Le numero de téléphone suivant '.$phoneNumber.' ne correspond à aucun enregistrement';
        }

        return ['success' => $status, 'message' => $message];
    }

    public function checkPhoneNumber(Request $request, Response $response)
    {
        return $response->withJson($this->checkIfPhoneNumberCodeMatch($request));
    }

    public function checkEmail(Request $request, Response $response)
    {
        
    }
   
}
