<?php

namespace App\Controllers\Auth;

use Cartalyst\Sentinel\Sentinel as Auth;
// use App\Auth\Hashing\HasherInterface;
use App\Controllers\Controller;
use App\Email\Mailer;
use App\Email\Templates\Welcome;
use App\Models\User;
use App\Session\Flash;
use App\Views\View;
use Slim\Http\Request;
use Slim\Http\Response;
use Noodlehaus\Config;

use App\Data\DummyData;

use App\Services\IceService;

use App\Models\Lead;


class RegisterController extends Controller
{
    protected $view;

    // protected $hasher;

    protected $auth;
    protected $flash;
    private $mailer;
    protected $dummyData;
    protected $iceService;
    protected $config;

    public function __construct(View $view, Auth $auth, Flash $flash, Mailer $mailer, DummyData $dummyData, Config $config)
    {
        $this->view = $view;
        // $this->hasher = $hasher;
        $this->auth = $auth;
        $this->flash = $flash;
        $this->mailer = $mailer;
        $this->dummyData = $dummyData;
        $this->config = $config;
    }

    public function index(Request $request, Response $response)
    {
        return $this->view->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response)
    {
        $data = $this->validateRegistration($request);

        $user = $this->auth->registerAndActivate([
            'first_name' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        
        // Send welcome email to newly registered user
        // $this->mailer->to($user->email, $user->username)->send(new Welcome($user));

        if ($this->auth->Authenticate([
            'email' => $data['email'],
            'password' => $data['password']
        ])) {
            $_SESSION['email'] = $data['email'];
            $this->flash->now('success', 'You were successfully registered!');
            return $response->withRedirect('/profile');
        }

        return $response->withRedirect('/');
    }

    protected function validateRegistration(Request $request)
    {
        return $this->validate($request, [
           'email' => ['required', 'email', ['exists', User::class]],
           'username' => ['required'],
           'password' => ['required'],
           'password_confirmation' => ['required', ['equals', 'password']],
        ]);
    }

    public function regions(Request $request, Response $response)
    {
        $regions = $this->dummyData->getRegions();
        return $response->withJson(['status' => 'success', 'data' => $regions]);
    }

    public function cities(Request $request, Response $response)
    {
        $cities = $this->dummyData->getCities($request->getParam('regionID'));
        return $response->withJson(['status' => 'success', 'data' => $cities]);
    }

    public function sectors(Request $request, Response $response)
    {
        $sectors = $this->dummyData->getActivityArea();
        return $response->withJson(['status' => 'success', 'data' => $sectors]);
    }

    public function branches(Request $request, Response $response)
    {
        $branch = $this->dummyData->getBranches($request->getParam('activityAreaID'));
        return $response->withJson(['status' => 'success', 'data' => $branch]);
    }

    public function subBranches(Request $request, Response $response)
    {
        $subBranch = $this->dummyData->getSubBranches($request->getParam('branchID'));
        return $response->withJson(['status' => 'success', 'data' => $subBranch]);
    }

    public function getSummaryInfos(Request $request, Response $response)
    {
        $content = '<form id="step-form-4" action="" method="POST" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="step" value="4">
                        <input type="hidden" name="cityID" value="'.$_SESSION['city']['id'].'">
                        <input type="hidden" name="regionID" value="'.$_SESSION['region']['id'].'">

                        <div class="credit__input">
                            <label for="summaryIce">ICE <sup><i class="icofont-star-alt-2"></i></sup></label>
                            <input class="input" id="summaryIce" name="summaryIce" type="text" placeholder="Numéro ICE" required maxlength="15" autocomplete="off" value="'.$_SESSION['ice'].'" readonly>
                            <i class=" input__load"></i>
                            <span id="summaryIceError" class="msg-info text-primary-red"></span>
                        </div>

                        <div class="both_input">
                            <div class="credit__input column one-third" style="margin-left: 0;">
                                <label for="summaryName">Nom <sup><i class="icofont-star-alt-2"></i></sup></label>
                                <input class="input" id="summaryName" name="summaryName" type="text" placeholder="Nom" required autocomplete="off" value="'.$_SESSION['name'].'" readonly>
                                <i class=" input__load"></i>
                                <span id="summaryNameError" class="msg-info text-primary-red"></span>
                            </div>
                            <div class="credit__input column one-third">
                                <label for="summaryFirstName">Prénom(s) <sup><i class="icofont-star-alt-2"></i></sup></label>
                                <input class="input" id="summaryFirstName" name="summaryFirstName" type="text" placeholder="Prénom(s)" required autocomplete="off" value="'.$_SESSION['firstName'].'" readonly>
                                <i class=" input__load"></i>
                                <span id="summaryFirstNameError" class="msg-info text-primary-red"></span>
                            </div>
                            <div class="credit__input column one-third">
                                <label for="summaryPhone">Téléphone mobile <sup><i class="icofont-star-alt-2"></i></sup></label>
                                <input class="input" id="summaryPhone" name="summaryPhone" type="text" placeholder="06XXXXXXXX" required autocomplete="off" value="'.$_SESSION['phoneSubscribe'].'" readonly>
                                <i class=" input__load"></i>
                                <span id="summaryPhoneError" class="msg-info text-primary-red"></span>
                            </div>
                        </div>

                        <div class="both_input">
                            <div class="credit__input column one-second" style="margin-left: 0;">
                                <label for="summaryAddress">Adresse <sup><i class="icofont-star-alt-2"></i></sup></label>
                                <input class="input" id="summaryAddress" name="summaryAddress" type="text" placeholder="Adresse" required autocomplete="off" value="'.$_SESSION['address'].'" readonly>
                                <i class=" input__load"></i>
                                <span id="summaryAddressError" class="msg-info text-primary-red"></span>
                            </div>
                            <div class="credit__input column one-second">
                                <label for="summaryEmail">Email <sup><i class="icofont-star-alt-2"></i></sup></label>
                                <input class="input" id="summaryEmail" name="summaryEmail" type="text" placeholder="Email" required autocomplete="off" value="'.$_SESSION['emailSubscribe'].'" readonly>
                                <i class=" input__load"></i>
                                <span id="summaryEmailError" class="msg-info text-primary-red"></span>
                            </div>
                        </div>

                        <div class="both_input">
                            <div class="credit__input column one-second">
                                <label for="summaryCity">Ville<sup><i class="icofont-star-alt-2"></i></sup></label>
                                <input class="input" id="summaryCity" name="summaryCity" type="text" placeholder="" required autocomplete="off" value="'.$_SESSION['city']['name'].'" readonly>
                                <i class=" input__load"></i>
                                <span id="summaryCityError" class="msg-info text-primary-red"></span>
                            </div>
                            <div class="credit__input column one-second">
                                <label for="summaryRegion">Region<sup><i class="icofont-star-alt-2"></i></sup></label>
                                <input class="input" id="summaryRegion" name="summaryRegion" type="text" placeholder="" required autocomplete="off" value="'.$_SESSION['region']['name'].'" readonly>
                                <i class=" input__load"></i>
                                <span id="summaryRegionError" class="msg-info text-primary-red"></span>
                            </div>
                        </div>

                        <p>Vous êtes : <span id="summaryCompanyStatus">'.$_SESSION['companyStatus'].'</span></p>
                        <br>
                        <p>Votre secteur d\'activité: <span id="summaryActivity">'.$_SESSION['sector']['name'].'</span></p>

                        <br><br>

                        <div class="column_attr clearfix align_center step-pause">
                            <a id="back-step" class="btn text-light sweep-to-right-primary-red ml-1 trigger-previous-step" href="#" style="background-color: #c40f11">
                                <i class="icofont icofont-arrow-left"></i>
                                <span>Revenir</span>
                            </a>
                            <a id="validate-step" class="btn text-light sweep-to-right-primary-red ml-1 trigger-next-step" href="#" style="background-color: #c40f11">
                                <span>Valider</span>
                                <i class="icofont icofont-arrow-right"></i>
                            </a>
                        </div>
                    </form>';

        return $response->withJson($content);
    }

    public function getServiceTerms(Request $request, Response $response)
    {
        $content = '<form id="step-form-5" action="" method="POST" enctype="multipart/form-data" autocomplete="off" data-stay-display="1">
                        <input type="hidden" name="step" value="5">
                        <input id="acceptedTermsOfService" type="hidden" name="accepted" value="0">

                        <div class="credit__input">
                            <label>Condition d\'utilisation du service</label>
                            <textarea id="termsOfService" name="termsOfService" rows="8" style="width: 100%" readonly>
                                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?
                            </textarea>
                            <span id="termsOfServiceError" class="msg-info text-primary-red"></span>
                        </div>

                        <div class="column_attr clearfix align_center step-pause">
                            <a class="btn text-light sweep-to-right-primary-red ml-1 trigger-next-step" style="background-color: #c40f11" href="#" onclick="setAccepted(\'0\')">
                                <i class="icofont icofont-arrow-left"></i>
                                <span>Refuser</span>
                            </a>
                            <a class="btn text-light sweep-to-right-primary-red ml-1 trigger-next-step" style="background-color: #c40f11" href="#" onclick="setAccepted(\'1\')">
                                <span>Accepter</span>
                                <i class="icofont icofont-arrow-right"></i>
                            </a>
                        </div>

                        <script>
                            function setAccepted(value) {
                                document.getElementById("acceptedTermsOfService").value = value;
                            }
                        </script>
                    </form>';
        return $response->withJson($content);
    }

    public function getPaymentSummary(Request $request, Response $response)
    {
        $abonnement = '';

        foreach($_SESSION['subscriptionChoice'] as $subscriptionChoise) {
            $abonnement .= '<tr>
                                <td>'.$subscriptionChoise['id'].'</td>
                                <td>'.$subscriptionChoise['volume'].'</td>
                                <td>'.$subscriptionChoise['amount'].'</td>
                                <td>'.$subscriptionChoise['managementFees'].'</td>
                                <td>'.$subscriptionChoise['canal'].'</td>
                                <td>
                                    <i class="icon-pocket"></i>
                                </td>
                            </tr>';
        }

        $littleTable = '<table>
                            <thead>
                                <tr>
                                    <th colspan="2">Récapitulatif du paiement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td align="left">Abonnement</td>
                                    <td>S</td>
                                </tr>
                                <tr>
                                    <td align="left">Montant HT</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td align="left">Frais de gestion</td>
                                    <td>250</td>
                                </tr>
                                <tr>
                                    <td align="left">Total HT</td>
                                    <td>250</td>
                                </tr>
                                <tr>
                                    <td align="left">TVA (20%)</td>
                                    <td>50</td>
                                </tr>
                                <tr>
                                    <td align="left">Montant total TTC</td>
                                    <td>300</td>
                                </tr>
                            </tbody>
                        </table>';

        $content = '<form id="step-form-7" action="" method="POST" enctype="multipart/form-data" autocomplete="off" data-stay-display="1">
                        <input type="hidden" name="step" value="7">

                        <div class="both__input">
                            <div>
                                <label>Nous vous remercions pour le choix d\'abonnement de la formule suivante :</label>
                                <table>
                                    <tr>
                                        <th>#</th>
                                        <th>Volume</th>
                                        <th>Montant HT</th>
                                        <th>Frais de gestion</th>
                                        <th>Canal</th>
                                        <th>Choix</th>
                                    </tr>
                                    '.$abonnement.'
                                </table>
                            </div>
                        </div>
                    </form>';

            $content .= $littleTable;

        return $response->withJson($content);
    }

    public function getPayment(Request $request, Response $response)
    {
		$storeKey = $this->config->get('cmi.storeKey');

        $data = [
            'clientid' => $this->config->get('cmi.clientID'),
            'amount' => '699.00',
            'okUrl' => env('APP_URL').'/auth/payment-done',
            'failUrl' => env('APP_URL').'/auth/payment-failure',
            'TranType' => $this->config->get('cmi.transactionType'),
            'callbackUrl' => env('APP_URL').'/auth/payment-back',
            'shopurl' => env('APP_URL').'/auth/register',
            'currency' => '504',
            'rnd' => microtime(),
            'storetype' => '3D_PAY_HOSTING',
            'hashAlgorithm' => 'ver3',
            'lang' => 'fr',
            'refreshtime' => '5',
            'BillToName' => 'name',
            'BillToCompany' => 'billToCompany',
            'BillToStreet1' => '100 rue adress',
            'BillToCity' => 'casablanca',
            'BillToStateProv' => 'Maarif Casablanca',
            'BillToPostalCode' => '20230',
            'BillToCountry' => '504',
            'email' => 'email@domaine.com',
            'tel' => '00212645717187',
            'encoding' => 'UTF-8',
            'oid' => $_SESSION['orderNumber'],
        ];

        $postParams = array();
        $dataToForm = '';
        foreach ($data as $key => $value){
            array_push($postParams, $key);
            $dataToForm .= "<input type=\"hidden\" name=\"" .$key ."\" value=\"" .trim($value)."\" /><br />";
        }
        
        natcasesort($postParams);		

        $hashval = '';					
        foreach ($postParams as $param){				
            $paramValue = trim($data[$param]);
            $escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", $paramValue));	
                
            $lowerParam = strtolower($param);
            if($lowerParam != "hash" && $lowerParam != "encoding" )	{
                $hashval = $hashval . $escapedParamValue . "|";
            }
        }
        
        
        $escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));	
        $hashval = $hashval . $escapedStoreKey;
        
        $calculatedHashValue = hash('sha512', $hashval);  
        $hash = base64_encode (pack('H*',$calculatedHashValue));
        
        $dataToForm .= "<input type=\"hidden\" name=\"HASH\" value=\"" .$hash."\" /><br />";	

        $paymentForm = '<form id="cardPaymentForm" method="post" action="'.$this->config->get('cmi.paymentUrl').'" style="display:none;">'.$dataToForm.'</form>';
        
        $paymentOptions = '<div class="row">
                                <div class="col">
                                    '.$paymentForm.'
                                    <a href="javascript:{}" onclick="document.getElementById(\'cardPaymentForm\').submit();"><img class="img-fluid" src="./assets/img/media/visa-mastercard-logo.png" /></a>
                                </div>
                                <div class="col">
                                    <img class="img-fluid" src="./assets/img/media/fatourati_2.jpg" />
                                </div>
                            </div>';

        $content = '<div>
                        <label>Merci de choisir le mode de paiement</label><br>
                        '.$paymentOptions.'
                    </div>';

        return $response->withJson($content);
    }

    public function sendStep(Request $request, Response $response)
    {
        $step = $request->getParam('step');

        switch ($step) {
            case 0:

                // Unset session variable
                unset($_SESSION['ice']);
                unset($_SESSION['emailIsValidate']);
                unset($_SESSION['phoneNumberIsValidate']);
                unset($_SESSION['orderNumber']);

                $_SESSION['orderNumber'] = rand(100000,999999); 
    
                $ice = $request->getParam('ice');

                $items = [];

                // Get lead if exists
                $lead = Lead::where('ice', $ice)->first();

                // Check if lead exists
                if($lead){
                    $status = false;
                    $informations = [
                        'title' => 'ICE existant',
                        'message' => 'L\'ICE renseigné existe déjà en base de données',
                        'status' => 'error'
                    ];
                    $items['informations'] = $informations;
                } else {
                    // Get ICE information
                    // Check if company exists before continue
                    $iceService = new IceService($ice);
                    $companyInfos = $iceService->getICEInformations();

                    // upload ICE
                    if (!empty($request->getUploadedFiles())) {
                        $file = $request->getUploadedFiles()['ice-file'];
                        if ($file->getError() === UPLOAD_ERR_OK) {
                            $filePath = $this->moveUploadedFile(
                                'ice' . DS . 'tmps',
                                $file
                            );
                            // $logo = $filePath['upload_path'];
                            $iceFile = true;
                        }
                    }
                    else $iceFile = null;
        
                    if(!empty($ice) && !empty($iceFile)) {
        
                        $_SESSION['ice'] = $ice;
                        $_SESSION['ice-attachment'] = $filePath['upload_path'];
        
                        $status = true;
                        $items = [
                            'ICE de l\'entreprise' => $_SESSION['ice'],
                            'files' => [
                                [
                                    'name' => 'Attestation ICE',
                                    'path' => $_SESSION['ice-attachment'],
                                    'completePath' => $_SESSION['ice-attachment'],
                                    'ext' => pathinfo($_SESSION['ice-attachment'], PATHINFO_EXTENSION)
                                ]
                            ]
                        ];
        
                        // ICE Curl
                        // if(count($companyInfos) > 0) {
                        //     foreach($companyInfos as $value) {
                        //         $items[$value['name']] = $value['value'];
                        //     }
                        // }
        
                        foreach($items['files'] as &$file) {
                            if (in_array($file['ext'], ['png', 'jpeg', 'jpg'])) {
                                // It's image so prepare image bloc to display
                                $file['imageBlock'] = '<div class="image_frame image_item scale-with-grid aligncenter has_border" style="max-width: 80px">
                                    <div class="image_wrapper" >
                                        <img class="img-fluid" src="'.$file['completePath'].'" alt="img">
                                    </div>
                                </div>';
                            }
                        }
                    } else {
                        $status = false;
                        $items = [];
                        if(empty($ice)) {
                            $items['iceError'] = 'Veuillez fournir un ICE valide';
                        }
                        if(empty($iceFile)) {
                            $items['iceFileError'] = 'Veuillez charger votre attestation ICE (Formats acceptés *.pdf, *.jpeg et *.png)';
                        }
                    }
                }
    
                return $response->withJson(['lastStep' => false, 'status' => $status, 'items' => $items]);
                break;
            case 1:
                $phone = $_POST['phoneSubscribe'];
                $email = $_POST['emailSubscribe'];

                $lead = null; 

                // Get session ICE if exists
                if(!empty($_SESSION['ice'])) {
                    $ice = $_SESSION['ice'];
                    // Get lead if exists
                    $lead = Lead::where('ice', $ice)->first();
                }
    
                // For real
                if((preg_match('/^[0-9]{10}+$/', $phone)) && (filter_var($email, FILTER_VALIDATE_EMAIL)) && (!empty($_SESSION['phoneNumberIsValidate'])) && (!empty($_SESSION['emailIsValidate']))) {
                // For test
                // if((preg_match('/^[0-9]{10}+$/', $phone)) && (filter_var($email, FILTER_VALIDATE_EMAIL)) ) {
                // if((!empty($phone)) && (filter_var($email, FILTER_VALIDATE_EMAIL))) {
                    $_SESSION['phoneSubscribe'] = $phone;
                    $_SESSION['emailSubscribe'] = $email;
    
                    $status = true;
                    $items = [
                        'Numéro de téléphone' => $_SESSION['phoneSubscribe'],
                        'Adresse email' => $_SESSION['emailSubscribe'],
                    ];
                } else {
                    $status = false;
                    $items = [];
                    if(!preg_match('/^[0-9]{10}+$/', $phone)) {
                        $items['phoneSubscribeError'] = 'Veuillez renseigner une numéro valide';
                    }
                    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $items['emailError'] = 'Veuillez renseigner un email valide';
                    }
    
                    // Check if phone number has bees activate 
                    // Use this for real
                    if(empty($_SESSION['phoneNumberIsValidate'])) {
                        $items['phoneSubscribeError'] = 'Numéro de téléphone non activé';
                    }
                    // // Use this for test
                    // if(!preg_match('/^[0-9]{10}+$/', $phone)) {
                    //     $items['phoneSubscribeError'] = 'Numéro de téléphone non activé';
                    // }

                    // Check if phone number has bees activate 
                    // Use this for real
                    if(empty($_SESSION['emailIsValidate'])) {
                        $items['emailError'] = 'Adresse Email non activé';
                    }
                    // // Use this for test
                    // if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    //     $items['emailError'] = 'Adresse Email non activé';
                    // }
                }
                
                return $response->withJson(['lastStep' => false, 'status' => $status, 'items' => $items]);
                break;
            case 2:
                // Get all input in the form
                $name = $request->getParam('name');
                $firstName = $request->getParam('firstName');
                $address = $request->getParam('address');

                $city = $this->dummyData->getCityById( $request->getParam('city') );
                $region = $this->dummyData->getRegionById( $request->getParam('region') );
    
                $items = [];
                
                // Check input value status and build response
                if(!empty($name) && !empty($firstName) && !empty($address) && !empty($city) && !empty($region)) {
                    $_SESSION['name'] = $name;
                    $_SESSION['firstName'] = $firstName;
                    $_SESSION['address'] = $address;
                    $_SESSION['city'] = $city;
                    $_SESSION['region'] = $region;
    
                    $status = true;
                    $items = [
                        'Nom' => $_SESSION['name'],
                        'Prénom(s)' => $_SESSION['firstName'],
                        'Adresse' => $_SESSION['address'],
                        'Ville' => $_SESSION['city']['name'],
                        'Region' => $_SESSION['region']['name'],
                    ];
                } else {
                    $status = false;
                    if(empty($name)) {
                        $items['nameError'] = 'Veuillez renseigner le nom du contact';
                    }
                    if(empty($firstName)) {
                        $items['firstNameError'] = 'Veuillez renseigner le prénom du contact';
                    }
                    if(empty($address)) {
                        $items['addressError'] = 'Veuillez renseigner l\'adresse';
                    }
                    if(empty($city)) {
                        $items['cityError'] = 'Veuillez renseigner la ville';
                    }
                    if(empty($region)) {
                        $items['regionError'] = 'Veuillez renseigner la région';
                    }
                }

                return $response->withJson(['lastStep' => false, 'status' => $status, 'items' => $items]);
                break;
            case 3:
                // Get all input in the form
                $companyStatus = $request->getParam('companyStatus');

                $sector = $this->dummyData->getSectorById( $request->getParam('activityArea') );
                $branch = $this->dummyData->getBranchById( $request->getParam('branch') );
                $subBranch = $this->dummyData->getSubBranchById( $request->getParam('subBranch') );
    
                $items = [];
                
                // Check input value status and build response
                if( !empty($companyStatus) && !empty($sector) && !empty($branch) && !empty($subBranch) ) {
                    $_SESSION['companyStatus'] = $companyStatus;
                    $_SESSION['sector'] = $sector;
                    $_SESSION['branch'] = $branch;
                    $_SESSION['subBranch'] = $subBranch;
    
                    $status = true;
                    $items = [
                        'Status de l\'entreprise' => $_SESSION['companyStatus'],
                        'Secteur d\'activité' => $_SESSION['sector']['name'],
                        'Branche' => $_SESSION['branch']['name'],
                        'Sous-branche' => $_SESSION['subBranch']['name']
                    ];
                } else {
                    $status = false;
                    if(empty($companyStatus)) {
                        // $items['nameError'] = 'Veuillez renseigner le nom du contact';
                    }
                    if(empty($sector)) {
                        $items['activityAreaError'] = 'Veuillez choisir le secteur d\'activité';
                    }
                    if(empty($branch)) {
                        $items['branchError'] = 'Veuillez choisir la branche';
                    }
                    if(empty($subBranch)) {
                        $items['subBranchError'] = 'Veuillez choisir la sous-branche';
                    }
                }
    
                sleep(1);
                return $response->withJson(['lastStep' => false, 'status' => $status, 'items' => $items]);
                break;
            case 4:
                // Get all input in the form
                // $ice = $_POST['summaryIce'];
                // $cityID = $_POST['cityID'];
                // $regionID = $_POST['regionID'];
                // $name = $_POST['summaryName'];
                // $firstName = $_POST['summaryFirstName'];
                // $phoneNumber = $_POST['summaryPhone'];
                // $address = $_POST['summaryAddress'];
                // $email = $_POST['summaryEmail'];

                $ice = $request->getParam('summaryIce');
                $cityID = $request->getParam('cityID');
                $regionID = $request->getParam('regionID');
                $name = $request->getParam('summaryName');
                $firstName = $request->getParam('summaryFirstName');
                $phoneNumber = $request->getParam('summaryPhone');
                $address = $request->getParam('summaryAddress');
                $email = $request->getParam('summaryEmail');

    
                $items = [];
                
                // Check input value status and build response
                if($ice == $_SESSION['ice'] && intval($cityID) == $_SESSION['city']['id'] && intval($regionID) == $_SESSION['region']['id'] && $name == $_SESSION['name'] && $firstName == $_SESSION['firstName'] && $phoneNumber == $_SESSION['phoneSubscribe'] && $address == $_SESSION['address'] && $email == $_SESSION['emailSubscribe']) {
                    $status = true;
                    $items = [
                        'ICE Entreprise' => $_SESSION['ice'],
                        'Ville' => $_SESSION['city']['name'],
                        'Region' => $_SESSION['region']['name'],
                        'Nom contact principal' => $_SESSION['name'],
                        'Prénom(s) contact principal' => $_SESSION['firstName'],
                        'Numéro de téléphone' => $_SESSION['phoneSubscribe'],
                        'Adresse' => $_SESSION['address'],
                        'Adresse email' => $_SESSION['emailSubscribe'],
                    ];
                } else {
                    $status = false;
                    if($ice != $_SESSION['ice']) $items['summaryIceError'] = 'L\'ICE renseignez ne correspond pas aux enregistrements';
                    if(intval($cityID) != $_SESSION['city']['id']) $items['summaryCityError'] = 'La ville renseignée ne correspond pas aux enregistrements';
                    if(intval($regionID) != $_SESSION['region']['id']) $items['summaryRegionError'] = 'La région renseignée ne correspond pas aux enregistrements';
                    if($name != $_SESSION['name']) $items['summaryNameError'] = 'Le nom renseigné ne correspond pas aux enregistrements';
                    if($firstName != $_SESSION['firstName']) $items['summaryFirstNameError'] = 'Le(s) prénom(s) renseigné(s) ne correspond(ent) pas aux enregistrements';
                    if($phoneNumber != $_SESSION['phoneSubscribe']) $items['summaryIceError'] = 'Le numéro renseigné ne correspond pas aux enregistrements';
                    if($address != $_SESSION['address']) $items['summaryIceError'] = 'L\'adresse renseignée ne correspond pas aux enregistrements';
                    if($email != $_SESSION['emailSubscribe']) $items['summaryIceError'] = 'L\'email renseigné ne correspond pas aux enregistrements';
                }
                
                sleep(1);
                return $response->withJson(['lastStep' => false, 'status' => $status, 'items' => $items]);
                break;
            case 5:
                // Get all input in the form
                $termsOfService = intval( $request->getParam('accepted') );
    
                $items = [];
                
                // Check input value status and build response
                if($termsOfService == 1) {
                    $status = true;
                    $_SESSION['termsOfService'] = true;
                } else {
                    $status = false;
                    $items['termsOfServiceError'] = 'Veuillez lire et accepter les conditions d\'utilisation pour profiter de nos services';
                    $hideStep = true;
                }
    
                sleep(1);
                return $response->withJson(['lastStep' => false, 'status' => $status, 'items' => $items, 'hideStep' => !empty($hideStep) ? $hideStep : null]);
                break;
            case 6:
                $formula = $request->getParam('subscriptionFormulaRadios');

                // // OLD CODE
                // $choiceA = intval( $request->getParam('choiceACheck') );
                // $choiceB = intval( $request->getParam('choiceBCheck') );
                // $choiceC = intval( $request->getParam('choiceCCheck') );
                // $choiceS = intval( $request->getParam('choiceSCheck') );
                // // END OLD CODE

                $items = [];
                $_SESSION['subscriptionChoice'] = [];
                
                // // OLD CODE
                // // Check input value status and build response
                // if($choiceA || $choiceB || $choiceC || $choiceS) {
                //     $status = true;
                //     if(!empty($choiceA)) $_SESSION['subscriptionChoice'][] = ['id' => 'A', 'name' => 'Choise A', 'amount' => 1250, 'managementFees' => 250, 'canal' => 'Multicanal', 'volume' => 100];
                //     if(!empty($choiceB)) $_SESSION['subscriptionChoice'][] = ['id' => 'B', 'name' => 'Choise B', 'amount' => 4750, 'managementFees' => 250, 'canal' => 'Multicanal', 'volume' => 500];
                //     if(!empty($choiceC)) $_SESSION['subscriptionChoice'][] = ['id' => 'C', 'name' => 'Choise C', 'amount' => 8250, 'managementFees' => 250, 'canal' => 'Multicanal', 'volume' => 1000];
                //     if(!empty($choiceS)) $_SESSION['subscriptionChoice'][] = ['id' => 'S', 'name' => 'Choise S', 'amount' => 'PO(2)', 'managementFees' => 250, 'canal' => 'SVI', 'volume' => '-'];
                // } else {
                //     $status = false;
                //     $items['subscriptionChoiceError'] = 'Veuillez choisir au moins une des offres de la liste';
                //     $hideStep = true;
                // }
                // // END OLD CODE
                if(!empty($formula)) {
                    $status = true;
                } else {
                    $status = false;
                    $items['subscriptionChoiceError'] = 'Veuillez choisir au moins une des offres de la liste';
                    $hideStep = true;
                }

    
                sleep(1);
                return $response->withJson(['lastStep' => false, 'status' => $status, 'items' => $items, 'hideStep' => !empty($hideStep) ? $hideStep : null]);
                break;
            case 7:
    
                sleep(2);

                return $response->withJson(['lastStep' => false, 'status' => true, 'items' => array()]);
                break;
            // case 8:
    
            //     sleep(2);
            //     return $response->withJson(['lastStep' => true, 'status' => true, 'items' => array()]);
            //     break;
        }
    }

    public function payment(Request $request, Response $response)
    {
        return $this->view->render($response, 'auth/payment.twig');
    }

    public function paymentCallback(Request $request, Response $response)
    {
		$storeKey = $this->config->get('cmi.storeKey');
    
        $allPost = $request->getParsedBody();
        
        $postParams = array();
        foreach ($allPost as $key => $value){
            array_push($postParams, $key);				
        }
        
        
        natcasesort($postParams);		
        $hach = "";
        $hashval = "";					
        foreach ($postParams as $param){				
            $paramValue = html_entity_decode(preg_replace("/\n$/","",$allPost[$param]), ENT_QUOTES, 'UTF-8'); 

            $hach = $hach . "(!".$param."!:!".$allPost[$param]."!)";
            $escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", $paramValue));	
                
            $lowerParam = strtolower($param);
            if($lowerParam != "hash" && $lowerParam != "encoding" )	{
                $hashval = $hashval . $escapedParamValue . "|";
            }
        }
        
        $escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));	
        $hashval = $hashval . $escapedStoreKey;
        
        $calculatedHashValue = hash('sha512', $hashval);  
        $actualHash = base64_encode (pack('H*',$calculatedHashValue));
        
        $retrievedHash = $allPost["HASH"];

        if($retrievedHash == $actualHash)	{
            if($_POST["ProcReturnCode"] == "00") {
                echo "ACTION=POSTAUTH";	
            } else {
                echo "APPROVED";
            }
        } else {
               echo "FAILURE";
        }	
    }

    public function paymentDone(Request $request, Response $response)
    {
        return $this->view->make('auth/payment-done.twig');
    }

    public function paymentFailure(Request $request, Response $response)
    {
        return $this->view->make('auth/payment-failure.twig');
    }
}
