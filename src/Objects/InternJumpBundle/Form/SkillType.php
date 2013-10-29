<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class SkillType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('title')
        ;
    }

    public function getName() {
        return 'objects_internjumpbundle_skilltype';
    }

    public function getDefaultOptions(array $options) {
        $options['data_class'] = '\Objects\InternJumpBundle\Entity\Skill';
        return $options;
    }
}
