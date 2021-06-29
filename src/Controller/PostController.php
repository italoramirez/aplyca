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
            'controller_name' => 'PostController',
            'form'            => $form->createView()
        ]);
    }
}
