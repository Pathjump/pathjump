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

class CVCategoryAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string 
     */
    protected $baseRouteName = 'cv_category_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string 
     */
    protected $baseRoutePattern = 'cv_category';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper 
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('name')
                ->add('slug')
                ->add('parentCategory')
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
                ->add('name')
                ->add('slug')
                ->add('parentCategory')
                ->add('subCategories')
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
                ->add('name')
                ->add('slug')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Mahmoud
     * @param FormMapper $formMapper 
     */
    public function configureFormFields(FormMapper $formMapper) {
        //the current object id if exist
        $currentId = NULL;
        //check if this is an edit form
        if ($this->getSubject()->getId()) {
            //set the current object id
            $currentId = $this->getSubject()->getId();
        }
        $formMapper
                ->with('Required Fields')
                ->add('name')
                ->add('slug')
                ->end()
                ->with('Not Required Fields')
                ->add('parentCategory', 'entity', array(
                    'required' => false,
                    'class' => 'ObjectsInternJumpBundle:CVCategory',
                    'query_builder' => function(EntityRepository $er) use ($currentId) {
                        $qb = $er->createQueryBuilder('c')->where('c.parentCategory is null');
                        if ($currentId) {
                            $qb->andWhere('c.id != ' . $currentId);
                        }
                        return $qb;
                    },
                    'attr' => array('class' => 'chosen')))
                ->end()
        ;
    }

}

?>
