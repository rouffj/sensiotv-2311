<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $form->get('cgu')->getData()) {
            $entity = $form->getData();
            
            $entityManager->persist($entity);
            $entityManager->flush();
            dump($form->get('cgu')->getData(), $entity);
            // TODO: Your entity is ready to be inserted into DB
        }
        
        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/login", name="login")
     */
    public function login(): Response
    {
        return $this->render('user/signin.html.twig');
    }
}