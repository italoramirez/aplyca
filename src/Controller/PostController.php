<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Form\PostType;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
    /**
     * @Route("/registrar-post", name="RegistrarPost")
     */
    public function index(Request $request)
    {
        $post = new Posts();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $File = $form['foto']->getData();
            if ($File) {
                $originalFilename = pathinfo($File->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $File->guessExtension();
                try {
                    $File->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('ha ocurrido un error, sorry :c');
                }
                $post->setFoto($newFilename);
            }

            // ... persist the $product variable or any other work

            // return $this->redirectToRoute('app_product_list');
            /* } */
            $user = $this->getUser();
            $post->setUser($user);
            //entity manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            // $this->addFlash('exitoso', Posts::REGISTRO_CORRECTO);
            return $this->redirectToRoute('home');
        }
        return $this->render('post/index.html.twig', [
            'form'            => $form->createView()
        ]);
    }

    /**
     * @Route("/show-post/{id}", name="showPost")
     */
    public function showPost($id)
    {
        $em   = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Posts::class)->find($id);
        return $this->render('post/showPost.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/my-post/", name="allPost")
     */
    public function myPost(PaginatorInterface $paginator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $query = $em->getRepository(Posts::class)->findBy(['user' => $user]);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 2), /*page number*/
            4 /*limit per page*/
        );
        return $this->render('post/myPost.html.twig', [
            'post' => $query,
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit-post", name="editPost")
     */

    public function edit(Posts $posts, Request $request)
    {
        $form = $this->createForm(PostType::class, $posts);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $File = $form['foto']->getData();
            if ($File) {
                $originalFilename = pathinfo($File->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $File->guessExtension();
                try {
                    $File->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('ha ocurrido un error, sorry :c');
                }
                $posts->setFoto($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('allPost');
        }

        return $this->render('post/editPost.html.twig', [
            'posts' => $posts,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="deletePost", methods={"POST"})
     */

    public function delete(Posts $post, Request $request): Response
    {
        if ($this->isCsrfTokenValid('deleted' . $post->getId(),     $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }
        return $this->redirectToRoute('allPost');
    }
}
