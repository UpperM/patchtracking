<?php

namespace App\Form;

use App\Entity\Changelog;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Repository\ApplicationsRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChangelogType extends AbstractType
{
    private $applicationsRepository;
    private $userRepository;
    public function __construct(ApplicationsRepository $applicationsRepository, UserRepository $userRepository)
    {
        $this->applicationsRepository = $applicationsRepository;
        $this->userRepository = $userRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $users = $this->userRepository->findAll();
        $builder
            ->add('updateVersionTo')
            ->add('updateVersionFrom', HiddenType::class)
            ->add('status', HiddenType::class)
            ->add('updateScheduleAt', DateType::class, array(
                'widget' => 'single_text'
            ))
            ->add('updateBy', ChoiceType::class, [
                'choices' => [
                    "Users" => $users
                ],
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->getFullName();
                },
            ])
            ->add('application', HiddenType::class)
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this,'onPreSetData']
            )
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                [$this,'onPreSubmit']
            )
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Changelog::class,
        ]);
    }

    public function onPreSetData(FormEvent $event) {
        $changelog = $event->getData();
        $application = $changelog->getApplication();
        $changelog->setUpdateVersionTo($application->getLatestVersion());
    }

    public function onPreSubmit(FormEvent $event) {
        $changelog = $event->getData();
        $form = $event->getForm();
        $application = $form->getViewData()->getApplication();
        $changelog['updateVersionFrom'] = $application->getCurrentVersion();
        $changelog['status'] = 2;
        $changelog['application'] = $application;

        $event->setData($changelog);

    }


}
