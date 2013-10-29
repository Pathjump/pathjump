<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CVType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('objective')
                ->add('name')
                ->add('categories', NULL, array('required' => false))
        ;
    }

    public function getName() {
        return 'objects_internjumpbundle_cvtype';
    }

}
