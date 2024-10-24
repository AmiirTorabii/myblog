<?php


namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\PostType;
use App\Entity\Approval;
use App\Entity\PostStatus;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @OA\Post(
     *     path="/api/posts/create",
     *     summary="Create a new post",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "userid", "catid", "typeid"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="userid", type="string"),
     *             @OA\Property(property="catid", type="string"),
     *             @OA\Property(property="typeid", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Post created"),
     *     @OA\Response(response="400", description="Title and content are required.")
     * )
     */
    #[Route('/api/posts/create', name: 'api_post_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Decode the JSON request body
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (empty($data['title']) || empty($data['content'])) {
            return new JsonResponse(['error' => 'Title and content are required.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Create a new Post instance
        $post = new Post();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);
        $post->setPublishedAt(new \DateTimeImmutable());

        // Set the owner (assuming you have a way to get the current user)
        $user = $entityManager->getRepository(User::class)->find($data['userid']);
        $cat = $entityManager->getRepository(Category::class)->find($data['catid']);
        $type = $entityManager->getRepository(PostType::class)->find($data['typeid']);

        if ($user instanceof User) {
            $post->setOwner($user);
        }

        $approval = new Approval($post, $user, PostStatus::PENDING);
        $post->setType($type);
        $post->setStatus($approval);
        $post->addCategory($cat);

        // Persist the post and approval to the database
        $entityManager->persist($post);
        $entityManager->persist($approval);
        $entityManager->flush();

        // Return a JSON response with the created post
        return new JsonResponse([
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'category' => $cat->getName(),
            'content' => $post->getContent(),
            'publishedAt' => $post->getPublishedAt()->format(\DateTimeImmutable::RFC3339),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/get",
     *     summary="Get all posts",
     *     @OA\Response(response="200", description="A list of posts")
     * )
     */
    #[Route('/api/posts/get', methods: ['GET'])]
    public function getPosts(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        
        $postData = [];
    
        foreach ($posts as $post) {
            $categories = [];
            foreach ($post->getCategory() as $category) {
                $categories[] = [
                    'name' => $category->getName(),
                ];
            }
    
            // دریافت وضعیت پست
            $statusData = null;
            if ($post->getStatus() !== null) {
                $statusData = [
                    'changedTo' => $post->getStatus()->getChangedTo(),
                    'approvedAt' => $post->getStatus()->getApprovedAt()->format(\DateTimeImmutable::RFC3339),
                    'by' => $post->getStatus()->getBy()->getUsername(),
                ];
            }
    
            // اضافه کردن داده‌های پست به آرایه
            $postData[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'status' => $statusData, // وضعیت پست
                'categories' => $categories,
                'content' => $post->getContent(),
                'publishedAt' => $post->getPublishedAt()->format(\DateTimeImmutable::RFC3339),
            ];
        }
    
        return $this->json(['posts' => $postData], Response::HTTP_OK);
    }
    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="Delete a post",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="204", description="Post deleted successfully"),
     *     @OA\Response(response="404", description="Post not found")
     * )
     */
    #[Route('/api/posts/{id}', methods: ['DELETE'])]
    public function removePost(string $id, EntityManagerInterface $entityManager): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Post deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Patch(
     *     path="/api/posts/status",
     *     summary="Change the status of a post",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"postid", "userid", "status"},
     *             @OA\Property(property="postid", type="string"),
     *             @OA\Property(property="userid", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Post status updated successfully"),
     *     @OA\Response(response="404", description="Post not found"),
     *     @OA\Response(response="403", description="Access denied. Admin role required."),
     *     @OA\Response(response="400", description="Invalid status.")
     * )
     */
    #[Route('/api/posts/status', methods: ['PATCH'])]
    public function changeStatus(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        // Find the post by ID
        $post = $entityManager->getRepository(Post::class)->find($data['postid']);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found.'], Response::HTTP_NOT_FOUND);
        }

        // Get the current user
        $user = $entityManager->getRepository(User::class)->find($data['userid']);

        // Check if the user is an admin
        if (!$user || $user->getRole() != Role::ADMIN) {
            return new JsonResponse(['error' => 'Access denied. Admin role required.'], Response::HTTP_FORBIDDEN);
        }

        // Validate status
        $post_status = PostStatus::fromString($data['status']);

        if (!in_array($post_status, [PostStatus::Accept, PostStatus::Reject], true)) {
            return new JsonResponse(['error' => 'Invalid status.'], Response::HTTP_BAD_REQUEST);
        }

        $approval = $post->getStatus();
        $approval->setChangedTo($post_status);
        $post->setStatus($approval);

        $entityManager->persist($approval);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Post status updated successfully.'], Response::HTTP_OK);
    }
}