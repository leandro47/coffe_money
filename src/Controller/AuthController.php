<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private UserPasswordHasherInterface $encoder;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager)
    {
        $this->encoder = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/register', name: 'api_register', methods: 'POST')]
    public function register(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['username']);
        
        $encodedPassword = $this->encoder->hashPassword($user, $data['password']);
        $user->setPassword($encodedPassword);

        $em->persist($user);
        $em->flush();
        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
