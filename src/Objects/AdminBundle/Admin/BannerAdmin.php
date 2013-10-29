<?php

/**
 * Description of PageAdmin
 *
 * @author Ahmed
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Objects\InternJumpBundle\Entity\Banner;

class BannerAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string
     */
    protected $baseRouteName = 'banner_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string
     */
    protected $baseRoutePattern = 'banner';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('code')
                ->add('url')
                ->add('position')
                ->add('numberOfClicks')
                ->add('numberOfViews')
                ->add('fileName', NULL, array('template' => 'ObjectsAdminBundle:General:list_swf_file.html.twig'))
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
                ->add('code')
                ->add('url')
                ->add('position')
                ->add('numberOfClicks')
                ->add('numberOfViews')
                ->add('fileName', NULL, array('template' => 'ObjectsAdminBundle:General:show_swf_file.html.twig'))
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:show_image.html.twig'))
        ;
    }

    /**
     * this function configure the list action filters fields
     *
     * @author Ahmed
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('code')
                ->add('url')
                ->add('position')
                ->add('numberOfClicks')
                ->add('numberOfViews')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Ahmed
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper) {
        $newBanner = new Banner();;

        $formMapper
                ->with('Required Fields')
                ->add('position','choice',array('choices' => $newBanner->getValidPositions()))
                ->add('code')
                ->add('url')
                ->add('SWF', 'file', array('required' => false, 'label' => 'swf'))
                ->add('file', 'file', array('required' => false, 'label' => 'image'))
                ->end()
        ;
    }

}

?>
