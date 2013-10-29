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

class ReportAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string
     */
    protected $baseRouteName = 'report_user_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string
     */
    protected $baseRoutePattern = 'report_user';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('company')
                ->add('user')
                ->add('cv')
                ->add('reason')
                ->add('reasonText')
                ->add('createdAt')
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
                ->add('company')
                ->add('user')
                ->add('cv')
                ->add('reason')
                ->add('reasonText')
                ->add('createdAt')
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
                ->add('company')
                ->add('user')
                ->add('reason')
                ->add('reasonText')
                ->add('createdAt')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Ahmed
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->with('Required Fields')
                ->add('company')
                ->add('user')
                ->add('cv')
                ->add('reason')
                ->add('reasonText')
                ->end()
        ;
    }

}

?>
