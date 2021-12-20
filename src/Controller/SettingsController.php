<?php

namespace App\Controller;

use App\Services\Api\GithubApi;
use App\Entity\Applications;
use App\Form\SettingsType;
use App\Repository\ApplicationsRepository;
use App\Repository\UserRepository;
use App\Services\Api\ApplicationsApi;
use App\Services\Api\GLPIApi;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController
{

    private $params;
    private $manager;
    private $applicationsRepository;

    public function __construct(ParameterBagInterface $params,EntityManagerInterface $manager, ApplicationsRepository $applicationsRepository)
    {
        $this->params = $params;
        $this->manager = $manager;
        $this->applicationsRepository = $applicationsRepository;
    }
    /**
     * @Route("/settings", name="settings")
     * @Route("settings/{id}/edit", name="settings.edit")
    */
    public function index(HttpFoundationRequest $request, $id = null)
    {

        $navbar = $this->applicationsRepository->findAll();

        if($request->get('_route') == 'settings.edit') {
            $application = $this->applicationsRepository->find($id);
            $template = 'edit.html.twig';
        } else {
            $application = new Applications();
            $template = 'index.html.twig';
        }


        $form = $this->createForm(SettingsType::class, $application);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($application);
            $this->manager->flush();
            return $this->redirectToRoute('settings');
        }

        return $this->render('settings/' . $template, [
            'controller_name' => 'SettingsController',
            'applications' => $navbar,
            'form' => $form->createView(),
            'compatibleApiApplication' => $this->params->get('api')
        ]);
    }

    /**
     * @Route("/settings/{id}/delete", name="settings.delete")
    */
    public function delete($id) {

        $application = $this->applicationsRepository->find($id);
        $this->manager->remove($application);
        $this->manager->flush();
        return $this->redirectToRoute('settings');
    }

    /**
     * @Route("/settings/updateGithubReleases", name="settings.updateGithubReleases")
    */


    public function updateGithubReleases(GithubApi $githubApi)
    {


        // Get latest version from github and persist to DB
        $githubRespository = $this->applicationsRepository->findAllGithubRepository();
        foreach ($githubRespository as $g) {
            $latestVersion = $githubApi->fetchGitHubLatestRelease($g->getGithubRepository());
            $g->setLatestVersion($latestVersion);
            $this->manager->persist($g);
        }
        $this->manager->flush();

        return $this->redirectToRoute('settings');
    }

    /**
     * @Route("/settings/updateApplicationCurrentVersion", name="settings.updateApplicationCurrentVersion")
    */
    public function updateApplicationCurrentVersion (ApplicationsApi $applicationsApi) {

        $applications = $this->applicationsRepository->findAllApiName();

        foreach ($applications as $application) {
            $currentVersion = $applicationsApi->getVersion($application->getApiName());
            $application->setCurrentVersion($currentVersion);
            $this->manager->persist($application);
        }

        $this->manager->flush();

        return $this->redirectToRoute('settings');

    }

    /**
     * @Route("/settings/users", name="settings.users")
    */
    public function users (UserRepository $userRepository) {
        $navbar = $this->applicationsRepository->findAll();


        return $this->render('settings/users.html.twig', [
            'controller_name' => 'SettingsController',
            'applications' => $navbar,
            'users' => $userRepository->findAll()
        ]);
    }

    /**
     * @Route("/settings/users/{id}/delete", name="settings.user.delete")
    */
    public function deleteUser($id, UserRepository $userRepository) {
        $user = $userRepository->find($id);
        $this->manager->remove($user);
        $this->manager->flush();
        return $this->redirectToRoute('settings.users');
    }

}
