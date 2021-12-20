<?php

namespace App\Form;

use App\Entity\Changelog;
use App\Repository\ApplicationsRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangelogValidateType extends AbstractType
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
            ->add('updateVersionFrom')
            ->add('updateVersionTo')
            ->add('updateDate', DateType::class, array(
                'widget' => 'single_text'
            ))
            ->add('updateScheduleAt', DateType::class, array(
                'widget' => 'single_text'
            ))
            ->add('comment')
            ->add('status', HiddenType::class)
            ->add('updateBy', ChoiceType::class, [
                'choices' => [
                    "Utilisateurs" => $users
                ],
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->getFullName();
                },
            ])
            ->add('report', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.oasis.opendocument.text',
                            'application/rtf',
                            'text/plain'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
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
        //$changelog = $event->getData();
        //$application = $changelog->getApplication();
        //$changelog->setUpdateVersionTo($application->getLatestVersion());
    }

    public function onPreSubmit(FormEvent $event) {
        $changelog = $event->getData();
        $form = $event->getForm();
        $application = $form->getViewData()->getApplication();
        $changelog['status'] = 1;
        $changelog['application'] = $application;
        $application->setCurrentVersion($form->getViewData()->getUpdateVersionTo());
        $event->setData($changelog);
    }
}
