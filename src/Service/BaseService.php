<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BaseService
{
    private $session;
    private $defaultImagePath = '/assets/images/Users/user.png';
    private $uploadDir;
    private $entityManager;
    private $kernel;
    private $filesystem;

    public function __construct(ParameterBagInterface $parameterBag,EntityManagerInterface $entityManager , KernelInterface $kernel)
    {
        $this->uploadDir = $parameterBag->get('imagesUploadPath');
        $this->session = new Session();
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->filesystem = new Filesystem();
    }

    public function isConnected(): bool
    {
        return $this->session->get('user_id') !== null;
    }

    public function getUser(): ?User
    {
        $userId = $this->session->get('user_id');
        if ($userId) {
            return $this->entityManager->getRepository(User::class)->find($userId);
        }
        return null;
    }

    public function getUserRoles(): ?array
    {
        $user = $this->getUser();
        if ($user) {
            return $user->getProfil()->getRoles()->map(function($role) {
                return $role->getId();
            })->toArray();
        }
        return null;
    }

    public function hasRole(string $role): bool
    {
        $roles = $this->getUserRoles();
        return in_array($role, $roles);
    }

    public function checkConnectionAndRole(string $role): string
    {
        if (!$this->isConnected()) {
            return 'NOT_CONNECTED';
        }

        if (!$this->hasRole($role)) {
            return 'NOT_ROLE';
        }
        return 'AUTHORIZED';
    }


    public function uploadFile(?UploadedFile $file, string $subDirectory = 'Users'): array
    {
        $result = [
            'message' => '',
            'imagePath' => $this->defaultImagePath,
        ];

        if ($file && $file instanceof UploadedFile) {
            // Generate a unique filename
            $fileName = uniqid() . '.' . $file->guessExtension();
            // Define the relative image path
            $imagePath = '/assets/images/' . $subDirectory . '/' . $fileName;

            // Ensure the directory exists or create it if it doesn't
            $directoryPath = $this->uploadDir . '/' . $subDirectory;
            if (!is_dir($directoryPath)) {
                if (!mkdir($directoryPath, 0755, true) && !is_dir($directoryPath)) {
                    $result['message'] = sprintf('Directory "%s" was not created.', $directoryPath);
                    throw new FileException($result['message']);
                }
            }

            // Move the file to the target directory
            try {
                $file->move($directoryPath, $fileName);
                $result['imagePath'] = $imagePath;
                $result['message'] = 'File uploaded successfully.';
            } catch (FileException $e) {
                $result['message'] = $e->getMessage();
                throw $e;
            }
        } else {
            $result['message'] = 'No file uploaded or invalid file type. Using default image.';
        }

        return $result;
    }


    public function getColumnsFile($file, SerializerInterface $serializer)
    {
        $response = "";
        if (!empty($file['name'])) {
            $fileSize = $file['size'];
            $extensions_valides = array('csv', 'xls', 'xlsx');
            $fileName = $file['name'];
            $extension_upload = strtolower(substr(strrchr($fileName, '.'), 1));

            $fileError = $file['error'];
            $fileTmpLoc = $file['tmp_name'];
            if ($fileError > 0) {
                $response = "Erreur lors du transfert du fichier !!";
            } else {
                if (in_array($extension_upload, $extensions_valides)) {
                    $data = [];
                    if ($extension_upload === 'csv') {
                        if (($handle = fopen($fileTmpLoc, "r")) !== FALSE) {
                            while (($data = fgetcsv($handle, 1000000, ";")) !== FALSE) {
                                break;
                            }
                            fclose($handle);
                            $data = array_map("utf8_encode", $data);
                        }
                    } else {
                        $spreadsheet = IOFactory::load($fileTmpLoc);
                        $sheet = $spreadsheet->getActiveSheet();
                        $highestColumn = $sheet->getHighestColumn();
                        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                        // Read the header from row 13
                        $data = $sheet->rangeToArray(
                            'A13:' . $sheet->getHighestColumn() . '13',
                            NULL,
                            TRUE,
                            FALSE
                        )[0];
                    }

                    if (!empty($data)) {
                        $json = $serializer->serialize($data, 'json');
                        return array("response" => "success", "colonnes" => $json);
                    } else {
                        $response = "Erreur lors de la lecture du fichier !!";
                    }
                } else {
                    $response = "Extension de fichier incorrecte, importer un fichier csv, xls ou xlsx SVP !!";
                }
            }
        } else {
            $response = "Importer un fichier SVP !!";
        }
        return array("response" => $response);
    }


    public function uploadFileAnalyse(UploadedFile $file, string $uploadDir): array
    {
        $response = [
            'status' => 'ERROR',
            'url' => '',
            'message' => '',
        ];

        $fileError = $file->getError();
        if ($fileError > 0) {
            $response['message'] = "File transfer error!";
            return $response;
        }

        $extension = $file->getClientOriginalExtension();
        $validExtensions = ['csv', 'xls', 'xlsx'];

        if (!in_array(strtolower($extension), $validExtensions)) {
            $response['message'] = "Invalid file extension! Please upload a csv, xls, or xlsx file.";
            return $response;
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = sha1(uniqid()) . '.' . $extension;

        // try {
            $uploadPath = $this->kernel->getProjectDir() ."/public/". $uploadDir;
            $file->move($uploadPath, $newFilename);

            $response['status'] = 'SUCCESS';
            $response['url'] = $uploadDir . '/' . $newFilename;
            $response['message'] = "File uploaded successfully!";
        // } catch (FileException $e) {
        //     $response['message'] = "File upload error: " . $e->getMessage();
        // }

        return $response;
    }

    public function uploadFileAnalyseCaisse(UploadedFile $file, string $uploadDir): array
    {
        $response = [
            'status' => 'ERROR',
            'url' => '',
            'message' => '',
        ];
    
        $fileError = $file->getError();
        if ($fileError > 0) {
            $response['message'] = "File transfer error!";
            return $response;
        }
    
        $extension = $file->getClientOriginalExtension();
        $validExtensions = ['csv', 'xls', 'xlsx'];
    
        if (!in_array(strtolower($extension), $validExtensions)) {
            $response['message'] = "Invalid file extension! Please upload a csv, xls, or xlsx file.";
            return $response;
        }
    
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    
        // Normalize the filename to remove special characters
        $normalizedFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $originalFilename);
        $normalizedFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $normalizedFilename);
    
        $newFilename = sha1(uniqid()) . '.' . $extension;
    
        try {
            $uploadPath = $this->kernel->getProjectDir() . "/public/" . $uploadDir;
            $file->move($uploadPath, $newFilename);
    
            $response['status'] = 'SUCCESS';
            $response['url'] = $uploadDir . '/' . $newFilename;
            $response['message'] = "File uploaded successfully!";
        } catch (FileException $e) {
            $response['message'] = "File upload error: " . $e->getMessage();
        }
    
        return $response;
    
    }
    
    function Role($codeFunction){
        $response=0;
        $session=new Session();
        if ($session->get('isConnected') != true || $session->get('user_id')==null){
            $response=0;
        }else{
            if($this->hasRole($codeFunction)){
                $response=1;
            }
            else
            {
                $response=2;
            }
        }
        return $response;
    }

    function RoleJson($codeFunction){
        $response=0;
        $session=new Session();
        if ($session->get('isConnected') != true || $session->get('user_id')==null){
            $response=0;
        }else{
            if($this->hasRole($codeFunction)){
                $response=1;
            }
            else
            {
                $response=2;
            }
        }
        if ($response !== 1) {
            throw new AccessDeniedHttpException('ERROR ACCESS: Vous n\'avez pas le rôle requis.');
        }
        return $response;
    }

    public function formatMonth($month) {
        $months = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
        ];
    
        return isset($months[$month]) ? $months[$month] : null;
    }
    
}
