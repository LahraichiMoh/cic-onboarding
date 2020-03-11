<?php

namespace App\Controllers;

use App\Views\View;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Email\Mailer;

use App\Models\Verification;
use App\Models\Lead;

// use App\Models\EmailVerification;
// use App\Models\PhoneNumberVerification;

use App\Services\IceService;

use App\Email\SenderVerificationCode;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Noodlehaus\Config;


class VerificationController extends Controller
{
    const EMAIL_SALT = '_cic_email_salt_grulog';
    const PHONE_NUMBER_SALT = '_cic_phone_number_salt_grulog';

    private $mailer;
    protected $view;
    protected $defaultCodeLength = 4;

    public function __construct(View $view, Mailer $mailer, Config $config)
    {
        $this->view = $view;
        $this->mailer = $mailer;
        $this->config = $config;
    }

    public function phoneNumber(Request $request, Response $response)
    {
        $lead = null;
        $verification = null;
        $phone = $request->getParam('phone');

        $code = $this->generatePhoneNumberSaltCode();

        // Get Lead if exists
        if( !empty($_SESSION['ice']) ) $lead = Lead::where('ice', $_SESSION['ice'])->first();

        
        // If Lead exists found verification entity
        if($lead && $lead->verification_id) {
            $verification = Verification::find($lead->verification_id);

            // if(!$verification->phone_verified_at) return $response->withJson(['success' => true, 'message' => 'Entrez votre code dé vérification']);
            // else return $response->withJson(['success' => false, 'message' => 'Ce numéro a déjà été utilisé']);

            $verification = $verification->update([
                'phone' => $phone,
                'phone_code' => $code['complete'],
                'phone_created_at' => time(),
                'phone_code_generated_at' => time()
            ]);
        }

        // Else create new
        else {
            $verification = Verification::create([
                'phone' => $phone,
                'phone_code' => $code['complete'],
                'phone_created_at' => time(),
                'phone_code_generated_at' => time()
            ]);
        }

        return $response->withJson(['success' => true, 'message' => 'Le code de vérification a été envoyé']);


        // ACTIVATE THIS CODE AFTER TEST
        // // Call method to send sms at the user
        // if($this->sendSMS($phone, $code['short'].' est le code confidentiel pour compléter votre inscription. Attention! Ce code a valididté de 5 min. A ne communiquer à personne. CHECKINFO'))
        //     return $response->withJson(['success' => true, 'message' => 'Le code de vérification a été envoyé']);
        // else
        //     return $response->withJson(['success' => false, 'message' => 'Le code de vérification n\'a pas pu être envoyé']);

    }

    public function email(Request $request, Response $response)
    {
        $lead = null;
        $verification = null;
        $email = $request->getParam('email');

        $code = $this->generateEmailSaltCode();

        // Get Lead if exists
        if( !empty($_SESSION['ice']) ) $lead = Lead::where('ice', $_SESSION['ice'])->first();

        
        // If Lead exists found verification entity
        if($lead && $lead->verification_id) {
            $verification = Verification::find($lead->verification_id);

            // if(!$verification->phone_verified_at) return $response->withJson(['success' => true, 'message' => 'Entrez votre code dé vérification']);
            // else return $response->withJson(['success' => false, 'message' => 'Ce numéro a déjà été utilisé']);

            $verification = $verification->update([
                'email' => $email,
                'email_code' => $code['complete'],
                'email_created_at' => time(),
                'email_code_generated_at' => time()
            ]);
        }

        // Else create new
        else {
            $verification = Verification::create([
                'email' => $email,
                'email_code' => $code['complete'],
                'email_created_at' => time(),
                'email_code_generated_at' => time()
            ]);
        }

        return $response->withJson(['success' => true, 'message' => 'Le code de vérification a été envoyé']);


        // // Get Lead if exists
        // if( !empty($_SESSION['ice']) ) $lead = Lead::where('ice', $_SESSION['ice'])->first();

        // // // Get email in database
        // $verification = Verification::where('email', $email)->first();

        // // If email in db
        // if($verification) {
        //     // Email already in db but not verified
        //     if(!$verification->email_verified_at) return $response->withJson(['success' => true, 'message' => 'Entrez votre code de vérification']);
        //     else return $response->withJson(['success' => false, 'message' => 'Cette adresse email a déjà été utilisée !']);
        // } else {
        //     // Email not in db
        //     // Generate code and send code by email
        //     $code = $this->generateEmailSaltCode();

        //     if($lead) {
        //         // If lead already exists update Verification
        //         $verification = Verification::where('id', $lead->verification_id)->first();

        //         $verification = $verification->update([
        //             'email' => $email,
        //             'email_code' => $code['complete'],
        //             'email_created_at' => time(),
        //             'email_code_generated_at' => time()
        //         ]);

        //     } else {
        //         // If lead not exists create new Verification
        //         $verification = Verification::create([
        //             'email' => $email,
        //             'email_code' => $code['complete'],
        //             'email_created_at' => time(),
        //             'email_code_generated_at' => time()
        //         ]);
        //     }

        //     return $response->withJson(['success' => true, 'message' => 'Le code de vérification a été envoyé']);

        //     // if($verification) {
        //     //     // Send email
        //     //     $mail = new PHPMailer(true);
        //     //     $mail->isSMTP();                                            // Send using SMTP
        //     //     $mail->SMTPAuth   = true;                                   // Enable SMTP authentication

		//     //     $mail->Host = $this->config->get('smtp.host');
		//     //     $mail->Username = $this->config->get('smtp.username');
		//     //     $mail->Password = $this->config->get('smtp.password');

        //     //     // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        //     //     $mail->SMTPSecure = 'ssl';
        //     //     $mail->Port       = 465; 

        //     //     $mail->setFrom($this->config->get('smtp.username'), 'CheckInfo Validation');

        //     //     // Server
        //     //     // $mail->setFrom('verification-cic@maxmind.ma', 'Code de Validation CheckInfo');
        //     //     $mail->addAddress($email, 'User');
        //     //     $mail->Subject  = 'Code de Validation CheckInfo';

        //     //     // $mail->Body = 'Votre code de validation est le : '.$code['short'];
        //     //     // $htmlMailContent = $this->view->render($response, 'emails/verification-code.twig', array('name' => '', 'code' => $code['short']));
        //     //     $htmlMailContent = $this->view->make('emails/verification-code.twig', array('name' => '', 'code' => $code['short']));
        //     //     $mail->Body = $htmlMailContent;
        //     //     $mail->CharSet = 'UTF-8';
        //     //     $mail->IsHTML(true);

        //     //     if( $mail->send() )
        //     //         return $response->withJson(['success' => true, 'message' => 'Le code de vérification a été envoyé']);
        //     //     else
        //     //         return $response->withJson(['success' => false, 'message' => 'Le code de vérification n\'a pas pu être envoyé']);
        //     // }
        //     // else 
        //         return $response->withJson(['success' => false, 'message' => 'Le code de vérification n\'a pas pu être envoyé']);
        // }
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

        // You can save this in config 
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

        // NEW VERIFICATION VERSION
        $verification = Verification::where('phone', $phoneNumber)->first();

        if($verification) {
            $ice = !empty($_SESSION['ice']) ? $_SESSION['ice'] : '--';

            if(!$verification->lead_id) {
                $lead = Lead::create([
                    'ice' => $ice,
                    'verification_id' => $verification->id
                ]);
            } else {
                $lead = Lead::find($verification->lead_id);
            }

            $verification->update(['lead_id' => $lead->id]);

            $verification->update(['phone_verified_at' => time()]);
            
            $_SESSION['phoneNumberIsValidate'] = true;
            return ['success' => true, 'message' => 'Votre email a été validé avec succès'];
        } else {
            return ['success' => false, 'message' => 'L\'email ne correspond à aucun enregistrement'];
        }


        // // If phone number present in phone_number_verifications table - Database 
        // if($verification) {
        //     if($verification->phone_verified_at) $message = 'Ce numero a déjà été validé';
        //     else {
        //         if( sha1($code.self::PHONE_NUMBER_SALT) == $verification->phone_code){
        //             $verification->update(['phone_verified_at' => time()]);
        //             $_SESSION['phoneNumberIsValidate'] = true;
        //             $status = true;
        //             $message = 'Votre numéro de téléphone a été vérifié avec succès';
        //         } else {
        //             $message = 'Le code saisi est erroné';
        //         }
        //     }
        // } else {
        //     $message = 'Le numero de téléphone suivant '.$phoneNumber.' ne correspond à aucun enregistrement';
        // }

        // return ['success' => $status, 'message' => $message];
    }

    protected function checkIfEmailCodeMatch(Request $request)
    {
        $status = false;
        $message = '';
        $email = $request->getParam('email');
        $code = $request->getParam('code');

        // NEW VERIFICATION VERSION
        $verification = Verification::where('email', $email)->first();

        if($verification) {
            $ice = !empty($_SESSION['ice']) ? $_SESSION['ice'] : '--';

            if(!$verification->lead_id) {
                $lead = Lead::create([
                    'ice' => $ice,
                    'verification_id' => $verification->id
                ]);
            } else {
                $lead = Lead::find($verification->lead_id);
            }

            $verification->update(['lead_id' => $lead->id]);

            $verification->update(['email_verified_at' => time()]);
            
            $_SESSION['emailIsValidate'] = true;
            return ['success' => true, 'message' => 'Votre email a été validé avec succès'];
        } else {
            return ['success' => false, 'message' => 'L\'email ne correspond à aucun enregistrement'];
        }

        // // This code has been replace by cURL API request
        // $verification = Verification::where('email', $email)->first();

        // // If email present in email_verifications table - Database 
        // if($verification) {
        //     if($verification->email_verified_at) $message = 'Ce numero a déjà été validé';
        //     else {
        //         if( sha1($code.self::EMAIL_SALT) == $verification->email_code){
        //             // Replace by API request to update emailVerification
        //             $verification->update(['email_verified_at' => time()]);

        //             if($verification->email_verified_at) {

        //                 $lead = null; 

        //                 // Get session ICE if exists
        //                 if(!empty($_SESSION['ice'])) {
        //                     $ice = $_SESSION['ice'];
        //                     // Get lead if exists
        //                     $lead = Lead::where('ice', $ice)->first();

        //                     if(!$lead) {
        //                         $lead = Lead::create([
        //                             'ice' => $ice,
        //                             'verification_id' => $verification->id
        //                         ]);

        //                         $verification->update(['lead_id' => $lead->id]);
        //                     } else {
        //                         $verifOfLead = Verification::find($lead->verification_id);

        //                         // Update verification
        //                     }
        //                 }

        //                 $_SESSION['emailIsValidate'] = true;
        //                 $status = true;
        //                 $message = 'Votre email a été vérifié avec succès';
        //             }
        //         } else {
        //             $message = 'Le code saisi est erroné';
        //         }
        //     }
        // } else {
        //     $message = 'L\'adresse mail suivante '.$email.' ne correspond à aucun enregistrement';
        // }

        // return ['success' => $status, 'message' => $message];
    }

    public function checkPhoneNumber(Request $request, Response $response)
    {
        return $response->withJson($this->checkIfPhoneNumberCodeMatch($request));
    }

    public function checkEmail(Request $request, Response $response)
    {
        return $response->withJson($this->checkIfEmailCodeMatch($request));
    }

    public function getICE(Request $request, Response $response)
    {
        $data = [];
        $ice = $request->getParam('ice');

        // Check if company exists before continue
        $iceService = new IceService($ice);
        $companyInfos = $iceService->getICEInformations();

        if($companyInfos) {
            $data['success'] = true;
            $data['iceResponse'] = $companyInfos;

            //Save ice status in session
            // use this session value to check if ICE has been verified in register step 0
            $_SESSION['ice-checked'] = true;
            $_SESSION['companyInfos'] = $companyInfos;
        } else {
            $data['success'] = false;
            $data['iceResponse'] = null;
            $_SESSION['ice-checked'] = false;
            $_SESSION['companyInfos'] = null;
        }

        return $response->withJson($data);
    }
   
}
