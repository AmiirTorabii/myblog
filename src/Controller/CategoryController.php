<?php


namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @OA\Post(
     *     path="/api/categories/create",
     *     summary="Create a new category",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Category created"),
     *     @OA\Response(response="400", description="Name and description are required")
     * )
     */
    #[Route('/api/categories/create', methods: ['POST'])]
    public function addCategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['name']) || !isset($data['description'])) {
            return $this->json(['message' => 'Name and description are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $category = new Category();
        $category->setName($data['name']);
        $category->setDescription($data['description']);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->json(['message' => 'Category created', 'id' => $category->getId()], JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/delete/{id}",
     *     summary="Delete a category",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Category deleted"),
     *     @OA\Response(response="404", description="Category not found")
     * )
     */
    #[Route('/api/categories/delete/{id}', methods: ['DELETE'])]
    public function removeCategory(string $id): JsonResponse
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->json(['message' => 'Category not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return $this->json(['message' => 'Category deleted'], JsonResponse::HTTP_OK);
    }
}
?>