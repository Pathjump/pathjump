<?php

/**
 * Description of BranchAdmin
 *
 * @author Ahmed
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Route\RouteCollection;

class CVAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string
     */
    protected $baseRouteName = 'cv_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string
     */
    protected $baseRoutePattern = 'cv';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('user')
                ->add('createdAt')
                ->add('viewsCount')
                ->add('objective')
                ->add('describeYourself')
                ->add('name')
                ->add('isActive')
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
                ->add('user')
                ->add('skills')
                ->add('internships')
                ->add('employmentHistory')
                ->add('categories')
                ->add('createdAt')
                ->add('viewsCount')
                ->add('objective')
                ->add('describeYourself')
                ->add('name')
                ->add('isActive')
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
                ->add('user')
                ->add('skills')
                ->add('internships')
                ->add('employmentHistory')
                ->add('categories')
                ->add('createdAt')
                ->add('viewsCount')
                ->add('objective')
                ->add('describeYourself')
                ->add('name')
                ->add('isActive')
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
                ->add('objective')
                ->add('describeYourself')
                ->add('name')
                ->add('isActive')
                ->end()
        ;
    }

}

?>
