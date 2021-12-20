<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Services\Api\GLPIApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/registration", name="security.registration")
     */

    public function registration (EntityManagerInterface $manager, HttpFoundationRequest $request, UserPasswordEncoderInterface $encoder, GLPIApi $glpiApi)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $glpiId = $this->glpiApi->getUserId($user->getEmail());
            if ($glpiId) {
                $user->setGlpiId($glpiId);
            }

            $hash = $encoder->encodePassword($user,$user->getPassword());
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('security.login');
        }
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="security.login")
     */
    public function login(AuthenticationUtils $authenticationUtils) {

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout",name="security.logout")
     */
    public function logout() {

    }
}
