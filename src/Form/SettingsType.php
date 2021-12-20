<?php

namespace App\Form;

use App\Entity\Applications;
use App\Services\Api\GithubApi;
use PhpParser\Builder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{

    private $githubApi;
    private $params;

    public function __construct(GithubApi $githubApi, ParameterBagInterface $params)
    {
        $this->githubApi = $githubApi;
        $this->params = $params;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // Check if params are sets
        if ($this->params->get('api')) {
            $API = array_keys($this->params->get('api'));
        } else {
            $API = [];
        }

        $builder
            ->add('name')
            ->add('currentVersion')
            ->add('latestVersion')
            ->add('githubRepository')
            ->add('api_name', ChoiceType::class, [
                'choices' => [
                    'API' => $API,
                ],
                'choice_label' => function ($choice, $key, $value) {
                    return strtoupper($value);
                },
                'required'   => false,
                'attr' => ['class' => 'text-uppercase']
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $data = $event->getData();

            if (null == $data['latestVersion'] && null != $data['githubRepository']) {
                $data['latestVersion'] = $this->githubApi->fetchGitHubLatestRelease($data["githubRepository"]);
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Applications::class,
        ]);
    }
}
