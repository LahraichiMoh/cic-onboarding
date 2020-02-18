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

use App\Data\DummyData;


class RegisterController extends Controller
{
    protected $view;

    // protected $hasher;

    protected $auth;

    protected $flash;

    private $mailer;

    protected $dummyData;

    public function __construct(View $view, Auth $auth, Flash $flash, Mailer $mailer, DummyData $dummyData)
    {
        $this->view = $view;
        // $this->hasher = $hasher;
        $this->auth = $auth;
        $this->flash = $flash;
        $this->mailer = $mailer;
        $this->dummyData = $dummyData;
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

    public function sendStep(Request $request, Response $response)
    {
        $this->initializeUploadsFolder();
        
        $step = $request->getParam('step');

        switch ($step) {
            case 0:
                // Unset session variable
                unset($_SESSION['emailIsValidate']);
                unset($_SESSION['phoneNumberIsValidate']);
    
                $ice = $request->getParam('ice');
    
                // Get ICE information
                // Check if company exists before continue
                // $companyInfos = getICEInformations($ice);
    
                // // iceFile Treat
                // if ( 0 < $_FILES['ice-file']['error'] ) {
                //     $iceFile = null;
                // }
                // else {
                //     $tmpsIceName = time().'_'.$_FILES['ice-file']['name'];
                //     $iceTmpsPath = 'uploads/ice/tmps/' . $tmpsIceName;
                //     if(move_uploaded_file($_FILES['ice-file']['tmp_name'], $iceTmpsPath)) $iceFile = true;
                // }

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
                                    <img class="scale-with-grid" src="'.$file['completePath'].'" alt="img">
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
    
                return $response->withJson(['lastStep' => false, 'status' => $status, 'items' => $items]);
                break;
            case 1:
                $phone = $_POST['phoneSubscribe'];
                $email = $_POST['emailSubscribe'];
    
                if((preg_match('/^[0-9]{10}+$/', $phone)) && (filter_var($email, FILTER_VALIDATE_EMAIL)) && (!empty($_SESSION['phoneNumberIsValidate'])) && (!empty($_SESSION['emailIsValidate']))) {
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
                    if(empty($_SESSION['phoneNumberIsValidate'])) {
                        $items['phoneSubscribeError'] = 'Numéro de téléphone non activé';
                    }
    
                    // Check if phone number has bees activate 
                    if(empty($_SESSION['emailIsValidate'])) {
                        $items['emailError'] = 'Email non activé';
                    }
                }
                sleep(3);
                // Prepare response 
                $response['lastStep'] = false;
                $response['status'] = $status;
                $response['items'] = $items;
                echo json_encode($response);
                break;
            case 2:
                // Get all input in the form
                $name = $_POST['name'];
                $firstName = $_POST['firstName'];
                $address = $_POST['address'];
                $city = getSingleData( intval($_POST['city']), $cities );
                $region = getSingleData( intval($_POST['region']), $regions );
    
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
    
                sleep(3);
                // Prepare response 
                $response['lastStep'] = false;
                $response['status'] = $status;
                $response['items'] = $items;
                echo json_encode($response);
                break;
            case 3:
                // Get all input in the form
                $companyStatus = $_POST['companyStatus'];
                $sector = getSingleData( intval($_POST['activityArea']), $sectors );
                $branch = getSingleData( intval($_POST['branch']), $branches );
                $subBranch = getSingleData( intval($_POST['subBranch']), $subBranches );
    
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
    
                sleep(3);
                // Prepare response 
                $response['lastStep'] = false;
                $response['status'] = $status;
                $response['items'] = $items;
                echo json_encode($response);
                break;
            case 4:
                // Get all input in the form
                $ice = $_POST['summaryIce'];
                $cityID = $_POST['cityID'];
                $regionID = $_POST['regionID'];
                $name = $_POST['summaryName'];
                $firstName = $_POST['summaryFirstName'];
                $phoneNumber = $_POST['summaryPhone'];
                $address = $_POST['summaryAddress'];
                $email = $_POST['summaryEmail'];
    
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
                
                sleep(3);
                // Prepare response 
                $response['lastStep'] = false;
                $response['status'] = $status;
                $response['items'] = $items;
                echo json_encode($response);
                break;
            case 5:
                // Get all input in the form
                $termsOfService = intval($_POST['accepted']);
    
                $items = [];
                
                // Check input value status and build response
                if($termsOfService == 1) {
                    $status = true;
                    $_SESSION['termsOfService'] = true;
                } else {
                    $status = false;
                    $items['termsOfServiceError'] = 'Veuillez lire et accepter les conditions d\'utilisation pour profiter de nos services';
                    $response['hideStep'] = true;
                }
    
                sleep(3);
                // Prepare response 
                $response['lastStep'] = false;
                $response['status'] = $status;
                $response['items'] = $items;
                echo json_encode($response);
                break;
            case 6:
                // Get all input in the form
                $choiceA = intval($_POST['choiceACheck']);
                $choiceB = intval($_POST['choiceBCheck']);
                $choiceC = intval($_POST['choiceCCheck']);
                $choiceS = intval($_POST['choiceSCheck']);
    
                $items = [];
                $_SESSION['subscriptionChoice'] = [];
                
                // Check input value status and build response
                if($choiceA || $choiceB || $choiceC || $choiceS) {
                    $status = true;
                    if(!empty($choiceA)) $_SESSION['subscriptionChoice'][] = ['id' => 'A', 'name' => 'Choise A', 'amount' => 1250, 'managementFees' => 250, 'canal' => 'Multicanal', 'volume' => 100];
                    if(!empty($choiceB)) $_SESSION['subscriptionChoice'][] = ['id' => 'B', 'name' => 'Choise B', 'amount' => 4750, 'managementFees' => 250, 'canal' => 'Multicanal', 'volume' => 500];
                    if(!empty($choiceC)) $_SESSION['subscriptionChoice'][] = ['id' => 'C', 'name' => 'Choise C', 'amount' => 8250, 'managementFees' => 250, 'canal' => 'Multicanal', 'volume' => 1000];
                    if(!empty($choiceS)) $_SESSION['subscriptionChoice'][] = ['id' => 'S', 'name' => 'Choise S', 'amount' => 'PO(2)', 'managementFees' => 250, 'canal' => 'SVI', 'volume' => '-'];
                } else {
                    $status = false;
                    $items['subscriptionChoiceError'] = 'Veuillez choisir au moins une des offres de la liste';
                    $response['hideStep'] = true;
                }
    
                sleep(3);
                // Prepare response 
                $response['lastStep'] = false;
                $response['status'] = $status;
                $response['items'] = $items;
                echo json_encode($response);
                break;
            case 7:
    
                sleep(3);
                // Prepare response 
                $response['lastStep'] = false;
                $response['status'] = true;
                $response['items'] = [];
                echo json_encode($response);
                break;
            case 8:
    
                sleep(2);
                // Prepare response 
                $response['lastStep'] = true;
                $response['status'] = true;
                $response['items'] = [];
                echo json_encode($response);
                break;
        }
    }

    private function initializeUploadsFolder()
    {
        if (!file_exists('uploads/')) {
            mkdir('uploads/', 0777, true);
        }
        if (!file_exists('uploads/ice/')) {
            mkdir('uploads/ice/', 0777, true);
        }
        if (!file_exists('uploads/ice/tmps/')) {
            mkdir('uploads/ice/tmps/', 0777, true);
        }
        if (!file_exists('uploads/ice/customers/')) {
            mkdir('uploads/ice/customers/', 0777, true);
        }
    }
}
