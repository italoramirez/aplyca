<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
                    throw new \Exception('UPs! ha ocurrido un error, sorry :c');
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
    public function myPost()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $post = $em->getRepository(Posts::class)->findBy(['user' => $user]);
        return $this->render('post/myPost.html.twig', ['post' => $post]);
    }

    public function update()
    {
    }
}
