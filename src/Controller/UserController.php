<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * @OA\Post(
     *     path="/api/users/create",
     *     summary="Create a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password", "email", "role"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="role", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="User created"),
     *     @OA\Response(response="400", description="Validation errors")
     * )
     */
    #[Route('/api/users/create', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        
        $data = json_decode($request->getContent(), true);
        
        if(!isset($data)){
           return $this->json(['errors' => $data], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT)); 
        $user->setEmail($data['email']);
        $role = Role::from($data['role']);
        $user->setRole($role);
        
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['id' => $user->getId()], Response::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *     path="/api/users/login",
     *     summary="Log in a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Login successful"),
     *     @OA\Response(response="404", description="User not found"),
     *     @OA\Response(response="401", description="Invalid password")
     * )
     */
    #[Route('/api/users/login', methods: ['POST'])]
    public function loginUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
    
        $username = $data['username'];
        $password = $data['password']; 
    
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
    
        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    
        if (password_verify($password, $user->getPassword())) {
            return $this->json(['message' => 'Login :)'], Response::HTTP_OK);
        } else {
            return $this->json(['message' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/delete/{uuid}",
     *     summary="Delete a user",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="User deleted successfully"),
     *     @OA\Response(response="404", description="User not found")
     * )
     */
    #[Route('/api/users/delete/{uuid}', methods: ['DELETE'])]
    public function deleteUser(string $uuid, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $uuid]);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     @OA\Response(response="200", description="A list of users")
     * )
     */
    #[Route('/api/users', methods: ['GET'])]
    public function getUsers(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'uuid' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()
            ];
        }

        return $this->json(['users' => $userData], Response::HTTP_OK);
    }
}
