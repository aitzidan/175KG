<?php

namespace App\Controller\User;

use App\Repository\ProfilRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\MessageService;
use App\Service\BaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use App\Entity\Profil;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends AbstractController
{
    private ProfilRepository $profilRepository;
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;
    private MessageService $MessageService;
    private BaseService $BaseService;

    public function __construct(ProfilRepository $profilRepository,UserRepository $userRepository,RoleRepository $roleRepository,
    MessageService $MessageService,BaseService $BaseService)
    {
        $this->profilRepository = $profilRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
    }

    #[Route('/', name: 'base')]
    public function indexAction(Request $request, EntityManagerInterface $entityManager)
    {
        $session = new Session();
        $userId = $session->get('user_id');
        if($userId)
        {
            $user = $entityManager->getRepository(User::class)->find($userId);

            $user = $session->get('user');
            $isConnected = $session->get('isConnected');
            if ($isConnected == true) {
                return $this->redirectToRoute('listUsers');
            }
            else{
                return $this->redirectToRoute('login');
            }
            
        }
        else
        {
            return $this->redirect('/dashboard');
        }
    }

    // #[Route('/dashboard', name: 'dashboard')]
    // public function dashboardAction(Request $request, EntityManagerInterface $entityManager)
    // {
    //     $session = new Session();
    //     $isConnected = $session->get('isConnected');
       
    //     if($isConnected != true){
    //         return $this->redirectToRoute('login');
    //     }
    //     else{
    //         $userId = $session->get('user_id');
    //         $user = $entityManager->getRepository(User::class)->find($userId);
    //         return $this->render('Base/index.html.twig',
    //         array("util"=> $user));
    //     }
    // }


    #[Route('/login', name: 'login')]
    public function login(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new Session();
        $response = "";

        $data = ["username" => '', "password" => ''];

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            // Attempt to log in the user
            $loginResult = $this->userRepository->login($data);

            if ($loginResult['success']) {
                
                $user = $loginResult['user'];

                // Save user information in session
                $session->set('user_id', $user->getId());
                $session->set('username', $user->getUsername());
                $session->set('isConnected',true);
                // Redirect the user to a dashboard or another page
                return $this->redirectToRoute('tableauBoard'); 
            } 
            else {
                $response = $loginResult['message'];
            }
        }

        return $this->render('user/login.html.twig', [
            'controller_name' => 'UserController',
            'response' => $response
        ]);
    }


    #[Route('/logout', name: 'logout')]
    public function logout(Request $request, EntityManagerInterface $entityManager,BaseService $baseService): Response
    {
        $session = new Session();
        $user = $session->get('user');
        $session->remove('user');
        $session->set('isConnected',false);

        return $this->redirectToRoute('login');
    }

    #[Route('/user/insertUser', name: 'insertUser')]
    public function addUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new Session();

        $isConnected = $session->get('isConnected');
        
        if($isConnected == false){
            return $this->redirectToRoute('login');
        }

        
        $chckAccess = $this->BaseService->Role(61);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $userId = $session->get('user_id');
        $user = $entityManager->getRepository(User::class)->find($userId);

        $codeStatut = "";
        $response = "";
        $data = ["nom" => '',"tele" => '',"email" => '',"address" => ''];
        
        try {

            $profilsListe = $this->profilRepository->findAll();
            $rolesListe = $this->roleRepository->findAll();
            
            
            if ($request->isMethod('POST')) {
                $data = $_REQUEST;
                if(isset($data['profil'])){

                    $isEmail = $this->userRepository->findBy(['email'=>$data['email']]);
                    if(!$isEmail){
                        $data = $request->request->all();
                        $file = $request->files->get('picture');
        
                        // Find the profil by its ID (assuming 'profil' is passed as an ID)
                        $profil = $entityManager->getRepository(Profil::class)->find($data['profil'][0]);
        
                        // Prepare data to be passed to the repository function
                        $data['profil'] = $profil;
        
                        // Add the user
                        $user = $this->userRepository->addUser($data, $file);
                        $codeStatut = "OK";
                    }else{
                        $codeStatut = "EMAIL_EXIST";
                    }
                }else{
                    $codeStatut = "ERREUR-PARAMS-VIDE";
                }
                $response =  $this->MessageService->checkMessage($codeStatut);
            }   
        }
        catch (\Exception $e)
        {
            $codeStatut="ERREUR-EXCEPTION";
            $response = $this->MessageService->checkMessage($codeStatut);
        }
        
        return $this->render('user/addUser.html.twig', [
            'controller_name' => 'UserController',
            'profils' => $profilsListe,
            'roles' => $rolesListe,
            'data' => $data,
            'response' => $response,
            'codeStatut'=>$codeStatut,
            "util"=> $user
        ]);

    }

    // UPDATE PROFIL
    #[Route('/user/updateUser/{id}/', name: 'updateUser')]
    public function updateUser(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        
        $session = new Session();
        $codeStatut = "";
        $response = "";
        $isConnected = $session->get('isConnected');
        
        if($isConnected == false){
            return $this->redirectToRoute('login');
        }

        $chckAccess = $this->BaseService->Role(62);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $userId = $session->get('user_id');
        $user = $entityManager->getRepository(User::class)->find($userId);
        $isConnected = $session->get('isConnected');


        try {

            $response = "";
            $data = ["nom" => '',"tele" => '',"email" => '',"address" => '',"profil" => '',"active" => '',"picture" => ''];

            $profilsListe = $entityManager->getRepository(Profil::class)->findAll();
            $rolesListe = $entityManager->getRepository(Role::class)->findAll();

            $user = $this->userRepository->find($id);

            if ($user) 
            {

                $data = [
                    "nom" => $user->getUsername(),
                    "tele" => $user->getPhone(),
                    "email" => $user->getEmail(),
                    "address" => $user->getAddress(),
                    "profil" => $user->getProfil(),
                    "active" => $user->getIsActif(),
                    "picture" => $user->getImage()
                ];
    
                if ($request->getMethod() == 'POST') {

                    $data = $request->request->all();
                    $file = $request->files->get('picture');

                    // Find the profil by its ID (assuming 'profil' is passed as an ID)
                    $profil = $entityManager->getRepository(Profil::class)->find($data['profil'][0]);

                    // Prepare data to be passed to the repository function
                    $data['profil'] = $profil;
                    $this->userRepository->MAJUser($id, $data, $file);

                    $codeStatut="OK";
                    $response = $this->MessageService->checkMessage($codeStatut);
                }

                return $this->render('user/updateUser.html.twig', [
                    'controller_name' => 'UserController',
                    'rolesListe' => $rolesListe,
                    'roles' => $rolesListe,
                    'data' => $data,
                    'profils' => $profilsListe,
                    'codeStatut' => $codeStatut,
                    'response' => $response
                ]);
            } 
            else 
            {
                return $this->redirectToRoute('listUsers');
            }

        } 
        catch (\Exception $e) 
        {
            $codeStatut="ERREUR-EXCEPTION";
            $response = $this->MessageService->checkMessage($codeStatut);

        }

        return $this->render('user/updateUser.html.twig', [
            'controller_name' => 'UserController',
            'roles' => $rolesListe,
            'data' => $data,
            'profils' => $profilsListe,
            'response' => $response,
            'codeStatut' => $codeStatut,
            "util"=> $user
        ]);
    }


    #[Route('/user/deleteUser/{id}/', name: 'deleteUser')]
    public function deleteUser(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        
        $session = new Session();

        $isConnected = $session->get('isConnected');
        
        // if($isConnected == false){
        //     return $this->redirectToRoute('login');
        // }

        $authStatus = $this->BaseService->checkConnectionAndRole('REMOUSER');

        $chckAccess = $this->BaseService->Role(63);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }


        try 
        {
            $user = $this->userRepository->find($id);

            if (!$user) {
                return $this->json(['error' => $this->MessageService->checkMessage("PROFIL-NOT-EXIST")], Response::HTTP_NOT_FOUND);

            }

            $entityManager->remove($user);
            $entityManager->flush();

            return $this->json('OK', Response::HTTP_OK);
        } 
        catch (\Exception $e) 
        {
            return $this->json(['error' => $this->MessageService->checkMessage("ERREUR-EXCEPTION")], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }


    #[Route('/user/listUsers', name: 'listUsers')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        
        $session = new Session();

        $isConnected = $session->get('isConnected');

        $userId = $session->get('user_id');
        $user = $entityManager->getRepository(User::class)->find($userId);
        $isConnected = $session->get('isConnected');

        $chckAccess = $this->BaseService->Role(60);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            $users = [];
        }else{

            // Fetch all profiles from the repository
            $users = $this->userRepository->findAll();
        }


        // Render the Twig template and pass the profiles to it
        return $this->render('user/listUsers.html.twig', [
            'users' => $users,"util"=> $user
        ]);
    }


    // ADD PROFIL
    #[Route('/user/insertProfil', name: 'insertProfil')]
    public function insertProfil(Request $request, EntityManagerInterface $entityManager): Response
    {

        $session = new Session();
        $codeStatut = "";
        $response = "";
        $isConnected = $session->get('isConnected');
        $chckAccess = $this->BaseService->Role(65);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }
        if($isConnected == false){
            return $this->redirectToRoute('login');
        }

        $userId = $session->get('user_id');
        $user = $entityManager->getRepository(User::class)->find($userId);
        $isConnected = $session->get('isConnected');

        // $authStatus = $this->BaseService->checkConnectionAndRole('ADDPROF');

        // if ($authStatus === 'NOT_CONNECTED') {
        //     return $this->redirectToRoute('login');
        // } elseif ($authStatus === 'NOT_ROLE') {
        //     return $this->redirectToRoute('listUsers');
        // }

        $response = "";
        $data = ["libelle" => '',"description" => ''];
        $rolesListe = [];
        try {

            $rolesListe = $this->roleRepository->findAllGroupedByCategory();
            $data = ["libelle" => '',"description" => ''];

            if ($request->getMethod() == 'POST') 
            {

                $data = $request->request->all();
                $roles = $data['roles'] ?? [];

                $this->profilRepository->insertProfil($data, $roles);

                $response =  $this->MessageService->checkMessage($codeStatut);
                return $this->redirectToRoute('listProfils');

            }

        } 
        catch (\Exception $e)
        {
            // Gérer l'exception
            $codeStatut="ERREUR-EXCEPTION";
            $response = $this->MessageService->checkMessage($codeStatut);
        }
        return $this->render('user/addProfil.html.twig', [
            'controller_name' => 'UserController',
            'rolesListe' => $rolesListe,
            'data' => $data,
            'response' => $response,
            'codeStatut' => $codeStatut,
            "util"=> $user
        ]);
    }


    // UPDATE PROFIL
    #[Route('/user/updateProfil/{id}/', name: 'updateProfil')]
    public function updateProfil(Request $request, EntityManagerInterface $entityManager, $id): Response
    {

        $session = new Session();
        $codeStatut = "";
        $response = "";

        $isConnected = $session->get('isConnected');
        
        $chckAccess = $this->BaseService->Role(66);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $userId = $session->get('user_id');
        $user = $entityManager->getRepository(User::class)->find($userId);
        $isConnected = $session->get('isConnected');

        $authStatus = $this->BaseService->checkConnectionAndRole('UPDAPROF');

        // if ($authStatus === 'NOT_CONNECTED') {
        //     return $this->redirectToRoute('login');
        // } elseif ($authStatus === 'NOT_ROLE') {
        //     return $this->redirectToRoute('listUsers');
        // }


            $response = "";
            $data = ["libelle" => '', "description" => ''];

            try {

                $rolesListe = $this->roleRepository->findAllGroupedByCategory();

                $profil = $entityManager->getRepository(Profil::class)->find($id);

                if ($profil) 
                {

                    $data = [
                        "libelle" => $profil->getNom(),
                        "description" => $profil->getDescription()
                    ];
    
                    $profilRoles = [];
                    foreach ($profil->getRoles() as $role) {
                        $profilRoles[] = $role->getId();
                    }

                    //dd($profilRoles);
                    if ($request->getMethod() == 'POST') {

                        $data = $request->request->all();
                        $roles = $data['roles'] ?? [];

                        $codeStatut="OK";
                        $this->profilRepository->updateProfil($id, $data, $roles);
                        $response =  $this->MessageService->checkMessage($codeStatut);

                        return $this->redirectToRoute('listProfils');
                    }
                } 

                else 
                {
                    return $this->redirectToRoute('listProfils');
                }

            } 
            catch (\Exception $e) 
            {
                $response = $this->MessageService->checkMessage("ERREUR-EXCEPTION");
            }
            return $this->render('user/updateProfil.html.twig', [
                'controller_name' => 'UserController',
                'rolesListe' => $rolesListe,
                'profilRoles' => $profilRoles,
                'data' => $data,
                'response' => $response,"util"=> $user,
                'codeStatut' => $codeStatut,
            ]);

    }

    // LIST PROFILS
    #[Route('/user/listProfils', name: 'listProfils')]
    public function listProfils(ProfilRepository $profilRepository, EntityManagerInterface $entityManager): Response
    {
        
        $session = new Session();

        $isConnected = $session->get('isConnected');
        
        $chckAccess = $this->BaseService->Role(64);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $userId = $session->get('user_id');
        $user = $entityManager->getRepository(User::class)->find($userId);
        $isConnected = $session->get('isConnected');

        $authStatus = $this->BaseService->checkConnectionAndRole('LISTPROF');

        // if ($authStatus === 'NOT_CONNECTED') {
        //     return $this->redirectToRoute('login');
        // } elseif ($authStatus === 'NOT_ROLE') {
        //     return $this->redirectToRoute('listUsers');
        // }

        // Fetch all profiles from the repository
        $profils = $this->profilRepository->findAll();

        // Render the Twig template and pass the profiles to it
        return $this->render('user/listProfils.html.twig', [
            'profils' => $profils,"util"=> $user
        ]);
    }
    
    // REMOVE PROFIL
    #[Route('/user/deleteProfil/{id}/', name: 'deleteProfil')]
    public function deleteProfil(Request $request, EntityManagerInterface $entityManager, ProfilRepository $profilRepository, int $id): JsonResponse
    {
        
        $session = new Session();
        $isConnected = $session->get('isConnected');
        
        // if($isConnected == false){
        //     return $this->redirectToRoute('login');
        // }

        // $authStatus = $this->BaseService->checkConnectionAndRole('REMOPROF');
        // if ($authStatus === 'NOT_CONNECTED') {
        //     return $this->json(['error' => $authStatus], Response::HTTP_INTERNAL_SERVER_ERROR);
        // } elseif ($authStatus === 'NOT_ROLE') {
        //     return $this->json(['error' => $authStatus], Response::HTTP_INTERNAL_SERVER_ERROR);
        // }

        
        $chckAccess = $this->BaseService->Role(67);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }


        try 
        {
            $profil = $profilRepository->find($id);
            if (!$profil) {
                return $this->json(['error' => $this->MessageService->checkMessage("PROFIL-NOT-EXIST")], Response::HTTP_NOT_FOUND);
            }
           
            $entityManager->remove($profil);
            $entityManager->flush();

            return $this->json('OK', Response::HTTP_OK);
        } 
        catch (\Exception $e) 
        {
            return $this->json(['error' => $this->MessageService->checkMessage("ERREUR-EXCEPTION")], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}