<?php

/*
 * This file is part of the SymEdit package.
 *
 * (c) Craig Blanchette <craig.blanchette@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymEdit\Bundle\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class ImageType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'require_name' => true,
            'required' => true,
            'label' => 'Image',
            'show_image' => true,
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['show_image'] = $options['show_image'];
    }

    public function getParent()
    {
        return 'symedit_media';
    }

    public function getName()
    {
        return 'symedit_image';
    }
}