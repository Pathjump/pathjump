<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ManagerType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('loginName')
            ->add('password')
            ->add('locked')
            ->add('enabled')
            ->add('salt')
            ->add('createdAt')
            ->add('managerRole')
            ->add('company')
        ;
    }

    public function getName()
    {
        return 'objects_internjumpbundle_managertype';
    }
}
