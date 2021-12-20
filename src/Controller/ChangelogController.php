<?php

namespace App\Controller;
use App\Entity\Changelog;
use App\Form\ChangelogEditType;
use App\Form\ChangelogType;
use App\Form\ChangelogValidateType;
use App\Repository\ApplicationsRepository;
use App\Repository\ChangelogRepository;
use App\Services\Api\GLPIApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\String\Slugger\SluggerInterface;

class ChangelogController extends AbstractController
{
    private $applicationsRepository;
    private $manager;
    private $changelogRepository;
    private $glpiApi;

    public function __construct(EntityManagerInterface $manager, ChangelogRepository $changelogRepository, ApplicationsRepository $applicationsRepository, GLPIApi $glpiApi)
    {
        $this->applicationsRepository = $applicationsRepository;
        $this->manager = $manager;
        $this->changelogRepository = $changelogRepository;
        $this->glpiApi = $glpiApi;
    }

    /**
     * @Route("/changelog/{id}/delete", name="changelog.delete")
     */
    public function delete($id) {

        $changelog = $this->changelogRepository->find($id);
        $applicationId = $changelog->getApplication()->getId();
        if ($changelog->getGlpiTicketId()){
            $this->glpiApi->deleteTicket($changelog->getGlpiTicketId());
        }
        $this->manager->remove($changelog);
        $this->manager->flush();
        return $this->redirectToRoute('applications',['id' => $applicationId]);
    }

    /**
     * @Route("/changelog/{id}/validate", name="changelog.validate")
     */

    public function validate($id,HttpFoundationRequest $request, Changelog $changelog, SluggerInterface $slugger)
    {
        $applications = $this->applicationsRepository->findAll();

        if($request->get('_route') == 'changelog.edit') {
            $template = 'edit.html.twig';
            $form = $this->createForm(ChangelogType::class, $changelog);
        } else {
            $template = 'validate.html.twig';
            $form = $this->createForm(ChangelogValidateType::class, $changelog);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($changelog->getStatus() == 1 && $changelog->getGlpiTicketId()) {

                // if can get glpiId of assigned user, provide API User Id
                if ($changelog->getUpdateBy()->getGlpiId())
                {
                    $userId = $changelog->getUpdateBy()->getGlpiId();
                } else {
                    $userId = $this->glpiApi->getApiUserId();
                }


                $this->glpiApi->addTicketFollowUp($changelog->getGlpiTicketId(),$changelog->getComment(),$userId);
                $this->glpiApi->closeTicket($changelog->getGlpiTicketId(),$userId);
            }

            $reportFile = $form->get('report')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($reportFile) {
                $originalFilename = pathinfo($reportFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$reportFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $reportFile->move(
                        $this->getParameter('reports_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $changelog->setReportFilename($newFilename);
            }

            $this->manager->persist($changelog);
            $this->manager->flush();


            return $this->redirectToRoute('applications',['id' => $changelog->getApplication()->getId()]);
        }

        return $this->render('changelog/' . $template, [
            'controller_name' => 'ApplicationsController',
            'applications' => $applications,
            'form' => $form->createView(),
            'changelog' => $changelog,
            'application' => $changelog->getApplication()
        ]);
    }

    /**
     * @Route("/changelog/{id}/edit", name="changelog.edit")
     */
    public function edit($id,HttpFoundationRequest $request, Changelog $changelog)
    {
        $applications = $this->applicationsRepository->findAll();
        $form = $this->createForm(ChangelogType::class, $changelog);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->manager->persist($changelog);
            $this->manager->flush();

            return $this->redirectToRoute('applications',['id' => $changelog->getApplication()->getId()]);
        }

        return $this->render('changelog/edit.html.twig', [
            'controller_name' => 'ApplicationsController',
            'applications' => $applications,
            'form' => $form->createView(),
            'changelog' => $changelog,
            'application' => $changelog->getApplication()
        ]);
    }


    /**
     * @Route("/changelog/{id}", name="changelog.show")
     */
    public function show($id)
    {

        $changelog = $this->changelogRepository->find($id);
        return $this->render('changelog/show.html.twig', [
            'changelog' => $changelog
        ]);
    }

}
