<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('loginName')
            ->add('password')
            ->add('confirmationCode')
            ->add('locked')
            ->add('enabled')
            ->add('salt')
            ->add('createdAt')
            ->add('country')
            ->add('city')
            ->add('state')
            ->add('address')
            ->add('establishmentDate')
            ->add('email')
            ->add('telephone')
            ->add('fax')
            ->add('url')
            ->add('zipcode')
            ->add('Latitude')
            ->add('Longitude')
            ->add('image')
            ->add('companyRoles')
            ->add('professions')
        ;
    }

    public function getName()
    {
        return 'objects_internjumpbundle_companytype';
    }
}
