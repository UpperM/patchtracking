<?php

namespace App\Controller;

use App\Entity\Applications;
use App\Repository\ApplicationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(ApplicationsRepository $repo)
    {
        $applications = $repo->findAll();

        $isUpToDate = 0;
        $scheduledUpdate = 0;
        $needUpdate = 0;

        foreach ($applications as $application) {

            if ($application->getCurrentVersion() < $application->getLatestVersion()) {
                $needUpdate = $needUpdate + 1;
            } else {
                $isUpToDate = $isUpToDate + 1;
            }
            
            foreach($application->getChangelogs() as $changelog) {
                if ($changelog->getStatus() == 2) {
                    $scheduledUpdate =+ 1;
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'applications' => $applications,
            'isUpToDate' => $isUpToDate,
            'scheduledUpdate' => $scheduledUpdate,
            'needUpdate' => $needUpdate
        ]);
    }
}
