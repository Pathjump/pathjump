<?php

/**
 * Description of QuizAnswerAdmin
 *
 * @author Mahmoud
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class QuizResultAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string 
     */
    protected $baseRouteName = 'quiz_result_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string 
     */
    protected $baseRoutePattern = 'quiz_result';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper 
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('score')
                ->add('message')
                ->add('description')
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:list_image.html.twig'))
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
                ->add('score')
                ->add('message')
                ->add('description')
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:list_image.html.twig'))
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
                ->add('score')
                ->add('message')
                ->add('description')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Mahmoud
     * @param FormMapper $formMapper 
     */
    public function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->with('Required Fields')
                ->add('score')
                ->add('message')
                ->add('description',null, array('required' => false))
                ->add('file', 'file', array('required' => false, 'label' => 'image'))
                ->end()
        ;
    }
    
    /**
     * @author Ahmed
     */
    public function preUpdate($result) {
        $result->preUpload();
    }
    
    /**
     * @author Ahmed
     */
    public function postUpdate($result) {
        $result->upload();
    }

}

?>
