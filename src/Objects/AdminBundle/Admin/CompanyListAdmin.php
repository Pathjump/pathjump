<?php

/**
 * Description of CompanyListAdmin
 *
 * @author Mahmoud
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Doctrine\ORM\EntityRepository;

class CompanyListAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string 
     */
    protected $baseRouteName = 'company_list_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string 
     */
    protected $baseRoutePattern = 'company_list';

    /**
     * this function configure the list action fields
     * @param ListMapper $listMapper 
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('name')
                ->add('loginName')
                ->add('email')
                ->add('companyRoles')
                ->add('establishmentDate')
                ->add('telephone')
                ->add('fax')
                ->add('url', NULL, array('template' => 'ObjectsAdminBundle:General:list_url.html.twig'))
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('Latitude')
                ->add('Longitude')
                ->add('createdAt')
                ->add('locked')
                ->add('enabled')
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:list_image.html.twig'))
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                    )
                ))
        ;
    }

    /**
     * this function configure the show action fields
     * @param ShowMapper $showMapper 
     */
    public function configureShowField(ShowMapper $showMapper) {
        $showMapper
                ->add('id')
                ->add('name')
                ->add('loginName')
                ->add('email')
                ->add('companyRoles')
                ->add('establishmentDate')
                ->add('telephone')
                ->add('fax')
                ->add('url', NULL, array('template' => 'ObjectsAdminBundle:General:show_url.html.twig'))
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('Latitude')
                ->add('Longitude')
                ->add('createdAt')
                ->add('locked')
                ->add('enabled')
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:show_image.html.twig'))
                ->add('professions')
        ;
    }

    /**
     * this function configure the list action filters fields
     * @param DatagridMapper $datagridMapper 
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('id')
                ->add('name')
                ->add('loginName')
                ->add('email')
                ->add('companyRoles')
                ->add('establishmentDate')
                ->add('telephone')
                ->add('fax')
                ->add('url')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('createdAt')
                ->add('Latitude')
                ->add('Longitude')
                ->add('locked')
                ->add('enabled')
                ->add('professions')
        ;
    }

    /**
     * this function is for editing the routes of this class
     * @author Mahmoud
     * @param RouteCollection $collection 
     */
    protected function configureRoutes(RouteCollection $collection) {
        $collection->remove('delete')->remove('create')->remove('update');
    }

}

?>
