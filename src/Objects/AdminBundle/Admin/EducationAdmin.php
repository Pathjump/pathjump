<?php

/**
 * Description of EducationAdmin
 *
 * @author Mahmoud
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Objects\InternJumpBundle\Entity\Education;

class EducationAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string
     */
    protected $baseRouteName = 'education_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string
     */
    protected $baseRoutePattern = 'education';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
//                ->add('user')
//                ->add('schoolName')
//                ->add('fieldOfStudy')
//                ->add('startDate')
//                ->add('endDate')
//                ->add('additionalDetails')
//                ->add('grade')
//                ->add('degree')
                ->add('user')
                ->add('schoolName')
                ->add('underGraduate')
                ->add('major')
                ->add('minor')
                ->add('startDate')
                ->add('endDate')
                ->add('extracurricularActivity')
                ->add('relevantCourseworkTaken')
                ->add('cumulativeGPA')
                ->add('majorGPA')
                ->add('graduateDegree')
                ->add('undergraduateDegree')
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    )
                ))
        ;
    }

    /**
     * this function configure the show action fields
     * @author Ahmed
     * @param ShowMapper $showMapper
     */
    public function configureShowField(ShowMapper $showMapper) {
        $showMapper
                ->add('id')
//                ->add('user')
//                ->add('schoolName')
//                ->add('fieldOfStudy')
//                ->add('startDate')
//                ->add('endDate')
//                ->add('additionalDetails')
//                ->add('grade')
//                ->add('degree')
                                ->add('user')
                ->add('schoolName')
                ->add('underGraduate')
                ->add('major')
                ->add('minor')
                ->add('startDate')
                ->add('endDate')
                ->add('extracurricularActivity')
                ->add('relevantCourseworkTaken')
                ->add('cumulativeGPA')
                ->add('majorGPA')
                ->add('graduateDegree')
                ->add('undergraduateDegree')
        ;
    }

    /**
     * this function configure the list action filters fields
     * @todo add the date filters if sonata project implemented it
     * @author Ahmed
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('id')
//                ->add('user')
//                ->add('schoolName')
//                ->add('fieldOfStudy')
//                ->add('startDate')
//                ->add('endDate')
//                ->add('additionalDetails')
//                ->add('grade')
//                ->add('degree')
                ->add('user')
                ->add('schoolName')
                ->add('underGraduate')
                ->add('major')
                ->add('minor')
                ->add('startDate')
                ->add('endDate')
                ->add('extracurricularActivity')
                ->add('relevantCourseworkTaken')
                ->add('cumulativeGPA')
                ->add('majorGPA')
                ->add('graduateDegree')
                ->add('undergraduateDegree')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Mahmoud
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper) {
        $years = array_reverse(range(1980, date('Y')));
        $choices = array();
        foreach ($years as $year) {
            $choices[$year] = $year;
        }
        
        $education = new Education();
        $formMapper
//                ->with('Required Fields')
//                ->add('schoolName')
//                ->end()
//                ->with('Not Required Fields')
//                ->add('fieldOfStudy')
//                ->add('startDate')
//                ->add('endDate')
//                ->add('additionalDetails')
//                ->add('grade', 'choice', array('choices' => $education->getValidGrades()))
//                ->add('degree', 'choice', array('choices' => $education->getValidDegrees()))
//                ->end()
//                ->add('user')
                ->add('schoolName')
                ->add('underGraduate')
                ->add('major')
                ->add('minor')
                ->add('startDate', 'choice', array('required' => FALSE, 'empty_data' => null, 'choices' => $choices, 'attr' => array('data-placeholder' => "Choose a year ...")))
                ->add('endDate', 'choice', array('required' => FALSE, 'empty_data' => null, 'choices' => $choices, 'attr' => array('data-placeholder' => "Choose a year ...")))
                ->add('extracurricularActivity')
                ->add('relevantCourseworkTaken')
                ->add('cumulativeGPA')
                ->add('majorGPA')
                ->add('graduateDegree')
                ->add('undergraduateDegree')
        ;
    }

}

?>
