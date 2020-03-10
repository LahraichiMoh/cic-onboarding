<?php

namespace App\Controllers;

use App\Views\View;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Email\Mailer;
use App\Models\Verification;

use App\Models\EmailVerification;
use App\Models\PhoneNumberVerification;

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
        $phone = $request->getParam('phone');

        // NEW VERIFICATION VERSION
        $verification = Verification::where('phone', $phone)->first();

        // If phonenumber in db
        if($verification) {
            // Number phone already in db
            if(!$verification->phone_verified_at) return $response->withJson(['success' => true, 'message' => 'Entrez votre code dé vérification']);
            else return $response->withJson(['success' => false, 'message' => 'Ce numéro a déjà été utilisé']);
        } else {
            // Number phone not in db
            // Generate code and send code by SMS
            $code = $this->generatePhoneNumberSaltCode();

            $verification = Verification::create([
                'phone' => $phone,
                'phone_code' => $code['complete'],
                'phone_created_at' => time(),
                'phone_code_generated_at' => time()
            ]);


            if($verification) {

                // Call method to send sms at the user
                if($this->sendSMS($phone, $code['short'].' est le code confidentiel pour compléter votre inscription. Attention! Ce code a valididté de 5 min. A ne communiquer à personne. CHECKINFO'))
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
        $verification = null;
        $email = $request->getParam('email');

        // // Get email in database
        $verification = Verification::where('email', $email)->first();

        // // API usage to replace to replace the method above
        // // Replace '.' in adresse email
        // $emailEdit = str_replace('.', '~~', $email);

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => env('API_URL').'/email-verification/'.$emailEdit,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "GET",
        // ));

        // $result = curl_exec($curl);

        // curl_close($curl);
        // $emailVerification = json_decode($result)[0];
        // // End API usage


        // If email in db
        if($verification) {
            // Email already in db
            if(!$verification->email_verified_at) return $response->withJson(['success' => true, 'message' => 'Entrez votre code de vérification']);
            else return $response->withJson(['success' => false, 'message' => 'Cette adresse email a déjà été utilisé !']);
        } else {
            // Number phone not in db
            // Generate code and send code by email
            $code = $this->generateEmailSaltCode();

            $verification = Verification::create([
                'email' => $email,
                'email_code' => $code['complete'],
                'email_created_at' => time(),
                'email_code_generated_at' => time()
            ]);

            // // API Start - Save email and hash code and return email entity from the database

            // $data = [
            //     'email' => $email,
            //     'code' => $code['complete']
            // ];
            // $curl = curl_init();

            // curl_setopt_array($curl, array(
            //     CURLOPT_URL =>  env('API_URL').'/email-verification',
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => "",
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => "POST",
            //     CURLOPT_POSTFIELDS => json_encode($data),
            //     CURLOPT_HTTPHEADER => array(
            //         "Content-Type: application/json"
            //     ),
            // ));

            // $result = curl_exec($curl);

            // curl_close($curl);
            // $emailVerification = json_decode($result);
            // // API End

            // var_dump( json_encode($data) ); exit();

            if($verification) {
                // Call method to send email at the user
                // Send email
                // $sendMail = $this->mailer->to($email, '')->send(new SenderVerificationCode(['name' => '', 'code' => $code['short']]));

                $mail = new PHPMailer(true);
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                $mail->isSMTP();                                            // Send using SMTP
                // $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                // $mail->Username   = 'maxmind.ulrich@gmail.com';                     // SMTP username
                // $mail->Password   = 'MaxmindUlrich2019*';                               // SMTP password

		        $mail->Host = $this->config->get('smtp.host');
		        $mail->Username = $this->config->get('smtp.username');
		        $mail->Password = $this->config->get('smtp.password');


                // Server
                // $mail->Host       = 'mail.maxmind.ma';                    // Set the SMTP server to send through
                // $mail->Username   = 'verification-cic@maxmind.ma';                     // SMTP username
                // $mail->Password   = 'HHSkismalj88ç';                               // SMTP password

                // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                $mail->SMTPSecure = 'ssl';
                $mail->Port       = 465; 

                $mail->setFrom($this->config->get('smtp.username'), 'CheckInfo Validation');

                // Server
                // $mail->setFrom('verification-cic@maxmind.ma', 'Code de Validation CheckInfo');
                $mail->addAddress($email, 'User');
                $mail->Subject  = 'Code de Validation CheckInfo';

                // $mail->Body = 'Votre code de validation est le : '.$code['short'];
                // $htmlMailContent = $this->view->render($response, 'emails/verification-code.twig', array('name' => '', 'code' => $code['short']));
                $htmlMailContent = $this->view->make('emails/verification-code.twig', array('name' => '', 'code' => $code['short']));
                $mail->Body = $htmlMailContent;
                $mail->CharSet = 'UTF-8';
                $mail->IsHTML(true);

                if( $mail->send() )
                    return $response->withJson(['success' => true, 'message' => 'Le code de vérification a été envoyé']);
                else
                    return $response->withJson(['success' => false, 'message' => 'Le code de vérification n\'a pas pu être envoyé']);
            }
            else 
                return $response->withJson(['success' => false, 'message' => 'Le code de vérification n\'a pas pu être envoyé']);
        }
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

        // If phone number present in phone_number_verifications table - Database 
        if($verification) {
            if($verification->phone_verified_at) $message = 'Ce numero a déjà été validé';
            else {
                if( sha1($code.self::PHONE_NUMBER_SALT) == $verification->phone_code){
                    $verification->update(['phone_verified_at' => time()]);
                    $_SESSION['phoneNumberIsValidate'] = true;
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

    protected function checkIfEmailCodeMatch(Request $request)
    {
        $status = false;
        $message = '';
        $email = $request->getParam('email');
        $code = $request->getParam('code');

        // This code has been replace by cURL API request
        $verification = Verification::where('email', $email)->first();
        
        // // API usage to replace to replace the method above
        // // Replace '.' in adresse email
        // $emailEdit = str_replace('.', '~~', $email);

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => env('API_URL').'/email-verification/'.$emailEdit,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "GET",
        // ));

        // $result = curl_exec($curl);

        // curl_close($curl);
        // $emailVerification = json_decode($result)[0];
        // // End API usage
        

        // If email present in email_verifications table - Database 
        if($verification) {
            if($verification->email_verified_at) $message = 'Ce numero a déjà été validé';
            else {
                if( sha1($code.self::EMAIL_SALT) == $verification->email_code){
                    // Replace by API request to update emailVerification
                    $verification->update(['email_verified_at' => time()]);

                    // // Update here entity by API request
                    // //Start API usage - to update entity
                    // $data = [
                    //     'email' => $email,
                    //     'code' => sha1($code.self::EMAIL_SALT),
                    //     'email_verified_at' => time()
                    // ];
                    // $curl = curl_init();

                    // curl_setopt_array($curl, array(
                    //     CURLOPT_URL => env('API_URL').'/email-verification/'.$emailVerification->id,
                    //     CURLOPT_RETURNTRANSFER => true,
                    //     CURLOPT_ENCODING => "",
                    //     CURLOPT_MAXREDIRS => 10,
                    //     CURLOPT_TIMEOUT => 0,
                    //     CURLOPT_FOLLOWLOCATION => true,
                    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    //     CURLOPT_CUSTOMREQUEST => "PUT",
                    //     CURLOPT_POSTFIELDS => json_encode($data),
                    //     CURLOPT_HTTPHEADER => array(
                    //         "Content-Type: application/json"
                    //     ),
                    // ));

                    // $result = curl_exec($curl);

                    // curl_close($curl);
                    // $emailVerification = json_decode($result);

                    // // End API usage

                    if($verification->email_verified_at) {

                        $_SESSION['emailIsValidate'] = true;
                        $status = true;
                        $message = 'Votre email a été vérifié avec succès';
                    }
                } else {
                    $message = 'Le code saisi est erroné';
                }
            }
        } else {
            $message = 'L\'adresse mail suivante '.$email.' ne correspond à aucun enregistrement';
        }

        return ['success' => $status, 'message' => $message];
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
