<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\MediaBundle\Widget\Strategy;

use Sylius\Bundle\ResourceBundle\Model\RepositoryInterface;
use SymEdit\Bundle\WidgetBundle\Model\WidgetInterface;
use SymEdit\Bundle\WidgetBundle\Widget\Strategy\AbstractWidgetStrategy;
use Symfony\Component\Form\FormBuilderInterface;

class GalleryStrategy extends AbstractWidgetStrategy
{
    protected $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(WidgetInterface $widget)
    {
        $gallery = $this->repository->findOneBySlug($widget->getOption('slider'));

        if (!$gallery) {
            return null;
        }

        return $this->render('@SymEdit/Widget/gallery.html.twig', array(
            'gallery' => $gallery,
        ));
    }

    public function buildForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('slider', 'entity_property', array(
                'label' => 'Gallery',
                'help_block' => 'Choose gallery to display',
                'class' => $this->repository->getClassName(),
                'property' => 'title',
                'property_value' => 'slug',
            ))
        ;
    }

    public function getName()
    {
        return 'gallery';
    }

    public function getDescription()
    {
        return 'Image Gallery';
    }
}
