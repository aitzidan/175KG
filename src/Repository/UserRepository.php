<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;
use App\Service\BaseService;
   
/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{

    private LoggerInterface $logger;
    private BaseService $BaseService;

    public function __construct(ManagerRegistry $registry,LoggerInterface $logger,BaseService $BaseService)
    {
        parent::__construct($registry, User::class);
        $this->logger = $logger;
        $this->BaseService = $BaseService;
    }

    public function login($data)
    {
        $email = $data['email'];
        $password = $data['password'];

        // Find the user by username
        $user = $this->findOneBy(['email' => $email]);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Check if the password is correct

        if (password_verify($password, $user->getPassword())) {
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    }


    public function addUser(array $data, ?UploadedFile $file): User
    {
        $entityManager = $this->getEntityManager();

        try {
            $user = new User();
            $user->setUsername($data['nom']);
            $user->setEmail($data['email']);
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
            $user->setProfil($data['profil']);
            $user->setPhone($data['tele'] ?? null);
            $user->setAddress($data['address'] ?? null);

            // Use the FileUploadService to handle file uploads and set the user image
            $uploadResult = $this->BaseService->uploadFile($file,"User");
            $user->setImage($uploadResult['imagePath']);

            $isActive = isset($data['active']) && $data['active'] === 'on';
            $user->setIsActif($isActive);

            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        return $user;
    }


    public function MAJUser(int $id, array $data, ?UploadedFile $file = null): User
    {

        $entityManager = $this->getEntityManager();

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw new \Exception('User not found.');
        }

        try {
            $user->setUsername($data['nom']);
            $user->setEmail($data['email']);
            if (!empty($data['password'])) {
                $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
            }
            $user->setProfil($data['profil']);
            $user->setPhone($data['tele'] ?? null);
            $user->setAddress($data['address'] ?? null);

            // Handle the file upload if a file is provided
            if ($file) {
                $uploadResult = $this->BaseService->uploadFile($file,"User");
                $user->setImage($uploadResult['imagePath']);
            }

            $isActive = isset($data['active']) && $data['active'] === 'on';
            $user->setIsActif($isActive);

            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

        } catch (\Exception $e) {
            // Handle general exception
            throw new \Exception('An error occurred while updating the user: ' . $e->getMessage());
        }

        return $user;
    }

      
}