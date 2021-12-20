<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\UserProfileType;
use App\Repository\ApplicationsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserProfileController extends AbstractController
{

    private $manager;
    private $userRepository;
    private $applicationsRepository;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository, ApplicationsRepository $applicationsRepository)
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
        $this->applicationsRepository = $applicationsRepository;
    }

    /**
     * @Route("/user/profile", name="user.profile")
     */
    public function profile(HttpFoundationRequest $request, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createForm(RegistrationType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $hash = $encoder->encodePassword($user,$user->getPassword());
            $user->setPassword($hash);
            $this->manager->persist($this->getUser());
            $this->manager->flush();
            return $this->redirectToRoute('user.profile');
        }

        return $this->render('user/profile.html.twig', [
            'controller_name' => 'UserProfileController',
            'applications' => $this->applicationsRepository->findAll(),
            'form' => $form->createView()
        ]);
    }
}
