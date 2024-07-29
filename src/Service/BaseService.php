<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;

class BaseService
{
    private $session;
    private $defaultImagePath = '/assets/images/Users/user.png';
    private $uploadDir;
    private $entityManager;

    public function __construct(ParameterBagInterface $parameterBag,EntityManagerInterface $entityManager)
    {
        $this->uploadDir = $parameterBag->get('imagesUploadPath');
        $this->session = new Session();
        $this->entityManager = $entityManager;
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
                return $role->getName();
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
                        $data = $sheet->rangeToArray(
                            'A1:' . $sheet->getHighestColumn() . '1',
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


}
