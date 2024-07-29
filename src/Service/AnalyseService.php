<?php

namespace App\Service;

use App\Repository\AnalyseRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class AnalyseService
{
    private $session;
    private $defaultImagePath = '/assets/images/Users/user.png';
    private $uploadDir;
    private $entityManager;
    private $analyseRepo;
    public function __construct(EntityManagerInterface $entityManager , AnalyseRepository $analyseRepo)
    {
        $this->session = new Session();
        $this->entityManager = $entityManager;
        $this->analyseRepo = $analyseRepo;
    }

    public function isConnected(): bool
    {
        return $this->session->get('user_id') !== null;
    }

    public function getTypeFichier()
    {
        return $this->analyseRepo->getTypeFichier();
    }
}