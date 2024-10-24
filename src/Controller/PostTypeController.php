<?php

namespace App\Controller;

use App\Entity\PostType;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostTypeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @OA\Post(
     *     path="/api/posttype/create",
     *     summary="Create a new post type",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Post type created"),
     *     @OA\Response(response="400", description="Name and description are required")
     * )
     */
    #[Route('/api/posttype/create', methods: ['POST'])]
    public function addPostType(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['name']) || !isset($data['description'])) {
            return $this->json(['message' => 'Name and description are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $postType = new PostType();
        $postType->setName($data['name']);
        $postType->setDescription($data['description']);

        $this->entityManager->persist($postType);
        $this->entityManager->flush();

        return $this->json(['message' => 'Post type created', 'id' => $postType->getId()], JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *     path="/api/posttypes/delete/{id}",
     *     summary="Delete a post type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Post type deleted"),
     *     @OA\Response(response="404", description="Post type not found")
     * )
     */
    #[Route('/api/posttypes/delete/{id}', methods: ['DELETE'])]
    public function removePostType(string $id): JsonResponse
    {
        $postType = $this->entityManager->getRepository(PostType::class)->find($id);

        if (!$postType) {
            return $this->json(['message' => 'Post type not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($postType);
        $this->entityManager->flush();

        return $this->json(['message' => 'Post type deleted'], JsonResponse::HTTP_OK);
    }
}
?>