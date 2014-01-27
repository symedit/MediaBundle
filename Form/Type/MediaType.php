<?php

namespace SymEdit\Bundle\MediaBundle\Form\Type;

use SymEdit\Bundle\MediaBundle\Form\EventListener\FileTypeSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new FileTypeSubscriber($options));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'require_name' => true,
            'required' => true,
            'callback' => null,
            'file_label' => 'File',
            'file_help' => false,
            'name_label' => 'File Name',
            'name_help' => false,
            'validation_groups' => array($this, 'getValidationGroups'),
        ));
    }

    public function getValidationGroups(FormInterface $form)
    {
        $config = $form->getConfig();

        if ($config->getOption('require_name')) {
            $group = 'require_name';
        } else {
            $group = 'image_only';
        }

        return array($group);
    }

    public function getName()
    {
        return 'symedit_media';
    }
}
