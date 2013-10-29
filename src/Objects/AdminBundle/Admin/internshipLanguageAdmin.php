<?php

/**
 * Description of AskedQuestionAdmin
 *
 * @author ahmed
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;

class internshipLanguageAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string 
     */
    protected $baseRouteName = 'internship_language_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string 
     */
    protected $baseRoutePattern = 'internship_language';

    /**
     * this function configure the list action fields
     * @author ahmed
     * @param ListMapper $listMapper 
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->add('internship')
                ->add('language')
                ->add('spokenFluency')
                ->add('writtenFluency')
                ->add('readFluency')
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
                ->add('internship')
                ->add('language')
                ->add('spokenFluency')
                ->add('writtenFluency')
                ->add('readFluency')
        ;
    }

    /**
     * this function configure the list action filters fields
     * @todo add the date filters if sonata project implemented it
     * @author ahmed
     * @param DatagridMapper $datagridMapper 
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('spokenFluency')
                ->add('writtenFluency')
                ->add('readFluency')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author ahmed
     * @param FormMapper $formMapper 
     */
    public function configureFormFields(FormMapper $formMapper) {

        $formMapper
                ->add('language')
//                ->add('internship')
                ->add('spokenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Spoken'))
                ->add('writtenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Written'))
                ->add('readFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Read'))
        ;
    }

}

?>
