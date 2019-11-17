<?php


namespace App\Controller;


use App\Entity\BlogPost;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class BlogController
 * @package App\Controller
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    private const POSTS = [
        [
            'id' => 1,
            'slug' => 'hello-world',
            'title' => 'Hello World!'
        ],
        [
            'id' => 2,
            'slug' => 'another-post',
            'title' => 'This is another post'
        ],
        [
            'id' => 3,
            'slug' => 'last-example',
            'title' => 'This is the last example'
        ]
    ];

    /**
     * @param $page
     * @param Request $request
     * @return JsonResponse
     * @Route("/{page}", name="blog_list", requirements={"page"="\d+"})
     */
    public function list($page, Request $request)
    {
//        $limit = $request->get('limit', 10);
        $repository = $this->getDoctrine()->getRepository(BlogPost::class);
        $items = $repository->findAll();

        return $this->json(
            [
                'page'=>$page,
//                'limit'=>$limit,
                'data'=>array_map(function(BlogPost $item)
                {
                    return $this->generateUrl('blog_by_slug', ['slug'=>$item->getSlug()]);
                }, $items)
            ]
        );
    }

    /**
     * @param BlogPost $post
     * @return JsonResponse
     * @Route("/post/{id}", name="blog_by_id", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function post(BlogPost $post)
    {
        return $this->json($post);
    }

    /**
     * @param $post
     * @return JsonResponse
     * @Route("/post/{slug}", name="blog_by_slug", methods={"GET"})
     */
    public function postBySlug(BlogPost $post)
    {
        return $this->json($post);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/add", name="blog_add", methods={"POST"})
     */
    public function add(Request $request)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');

        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();

        return $this->json($blogPost);
    }

    /**
     * @param BlogPost $post
     * @return JsonResponse
     * @Route("/post/{id}", name="blog_delete", methods={"DELETE"})
     */
    public function delete(BlogPost $post)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($post);

        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}