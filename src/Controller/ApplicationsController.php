<?php

namespace App\Controller;

use App\Entity\Changelog;
use App\Form\ChangelogType;
use App\Repository\ApplicationsRepository;
use App\Repository\ChangelogRepository;
use App\Services\Api\ApplicationsApi;
use App\Services\Api\GLPIApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ApplicationsController extends AbstractController
{

    private $applicationsRepository;
    private $applicationsApi;
    private $manager;
    private $glpiApi;
    private $security;

    public function __construct(ApplicationsRepository $applicationsRepository,ApplicationsApi $applicationsApi, EntityManagerInterface $manager, GLPIApi $glpiApi, Security $security)
    {
        $this->applicationsRepository = $applicationsRepository;
        $this->applicationsApi = $applicationsApi;
        $this->manager = $manager;
        $this->glpiApi = $glpiApi;
        $this->security = $security;
    }


    /**
     * @Route("/applications/{id}", name="applications")
     */

    public function index($id,HttpFoundationRequest $request, ParameterBagInterface $params)
    {
        $applications = $this->applicationsRepository->findAll();
        $application = $this->applicationsRepository->find($id);

        $changelog = new Changelog();

        $changelog->setApplication($application);

        $form = $this->createForm(ChangelogType::class, $changelog);

        $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {

            if($params->get('glpi')['enable']) {
                $title = "Mises à jour de " . $application->getName() . ' vers ' . $changelog->getUpdateVersionTo();
                $updateScheduleAt = $changelog->getUpdateScheduleAt()->format('d/m/Y');
                $content = "Mise à jour de " . $application->getName() . ' depuis ' . $changelog->getUpdateVersionFrom() . ' vers ' . $changelog->getUpdateVersionTo() . ' planifié pour le ' . $updateScheduleAt;
                $userId = $this->security->getUser()->getGlpiId();
                $userAssignedId = $changelog->getUpdateBy()->getGlpiId();
                if (!$userAssignedId) {
                    $userAssignedId = $userId;
                }

                $ticketId = $this->glpiApi->addTicket($title,$content,$userId,$userAssignedId);
                $changelog->setGlpiTicketId($ticketId);
            }

            $this->manager->persist($changelog);
            $this->manager->flush();
            return $this->redirectToRoute('applications',['id' => $id]);


        }

        return $this->render('applications/index.html.twig', [
            'controller_name' => 'ApplicationsController',
            'applications' => $applications,
            'application' => $application,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/application/{id}/delete", name="application.delete")
     */
    public function delete($id) {

        $application = $this->applicationsRepository->find($id);
        $this->manager->remove($application);
        $this->manager->flush();
        return $this->redirectToRoute('settings');
    }




    /**
     * @Route("/application/{id}/currentversion", name="application.currentversion")
     */
    public function currentVersionById($id, HttpFoundationRequest $request) {

        $applicationName = $this->applicationsRepository->find($id)->getName();
        $applicationName = strtolower($applicationName);


        switch ($request->getMethod()) {
            case 'GET':
                $v = $this->applicationsApi->getVersion($applicationName);
                break;

            default:
                # code...
                break;
        }
        return new JsonResponse(array('current_version' => $v));
    }

    /**
     * @Route("/application/api/{name}/currentversion", name="application.api.currentversion")
     */
    public function currentVersionByApiName(String $name)
    {
        $v = $this->applicationsApi->getVersion($name);

        return new JsonResponse(array('current_version' => $v));
    }


}
