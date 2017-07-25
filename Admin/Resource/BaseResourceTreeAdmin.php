<?php

namespace TechPromux\Bundle\BaseBundle\Admin\Resource;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

namespace TechPromux\Bundle\BaseBundle\Admin\Resource;

/**
 * Class BaseResourceTreeAdmin
 * @package TechPromux\Bundle\BaseBundle\Admin\Resource
 */
abstract class BaseResourceTreeAdmin extends BaseResourceAdmin {


    protected $listModes = [
        'list' => array(
            'class' => 'fa fa-list fa-fw',
        ),
        'tree' => array(
            'class' => 'fa fa-align-left fa-fw',
        ),
    ];

    /**
     * 
     * @return \TechPromux\Bundle\BaseBundle\Manager\BaseResourceTreeManager
     */
    public function getResourceManager() {
        return parent::getResourceManager();
    }

    /**
     * 
     * @return \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree
     */
    public function getSubject() {
        return parent::getSubject();
    }

    /**
     * 
     * @param string $context
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQuery($context = 'list') {

        $query = parent::createQuery($context);

        $this->getResourceManager()->alterBaseQueryBuilder($query);

        $request = $this->getRequest();

        $main_parent_id = $request->get('main-parent-tree', null);
        if (!is_null($main_parent_id)) {
            $query = $this->getResourceManager()->alterQueryBuilderWithParentRoot($query, $main_parent_id);
        }

        $exclude_parent_id = $request->get('exclude-parent-tree', null);
        if (!is_null($exclude_parent_id)) {
            $query = $this->getResourceManager()->alterQueryBuilderExceptWithParentRoot($query, $exclude_parent_id);
        }
        return $query;
    }

    /**
     * 
     * @param \Sonata\AdminBundle\Route\RouteCollection $routes
     */
    public function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $routes) {
        parent::configureRoutes($routes);

        $routes->remove('show');
        $routes->remove('export');
        $routes->remove('batch');
        $routes->add('tree', 'tree');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {

        parent::configureDatagridFilters($datagridMapper);

        $datagridMapper
                ->add('codigo', null, array())
                ->add('nombre', null, array())
                ->add('parent.codigo', null, array())
                ->add('parent.nombre', null, array())
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper) {

        parent::configureListFields($listMapper);

        $listMapper
                ->addIdentifier('levelAndNombre', null, array())
                ->add('codigo', null, array())
                //->add('parent', null, array())
                //->add('descripcion', null, array())
        ;

        $listMapper->add('habilitado', null, array(
            'editable' => true,
            'row_align' => 'center',
            'header_style' => 'width: 100px',
        ));

        $listMapper->add('_action', 'actions', array(
            'row_align' => 'right',
            'header_style' => 'width: 90px',
            'actions' => array(
                'edit' => array(),
                'delete' => array(),
            )
        ));
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper) {

        parent::configureFormFields($formMapper);

        $object = $this->getSubject(); /* @var \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object */

        if ($object && $object->getParent() && is_null($object->getParent()->getParent())) {
            $object->setParent(null);
        }

        $formMapper
                ->with('form.label_group_general', array('class' => 'col-md-6'))
                ->add('codigo')
                ->add('nombre')
                ->add('descripcion', 'textarea', array(
                    'required' => false,
                    'attr'=>array('class'=>'html')
                ))
                ->end()
        ;

        $roots = $this->getResourceManager()->findRootElements();

        if ((is_null($object) || is_null($object->getId())) ? count($roots) > 0 : (count($roots) > 1 || (!is_null($object->getParent()) && !is_null($object->getParent()->getParent())))) {
            $formMapper
                    ->with('form.label_group_opciones', array('class' => 'col-md-6'));

            if ($this->getCustomParentAssociationFieldType() == 'sonata_type_model_list') {
                $formMapper->add('parent', 'sonata_type_model_list', array(
                    'required' => false,
                    'btn_add' => false,
                        //'btn_delete' => false
                        ), array('link_parameters' => array('exclude-parent-tree' => $this->getSubject()->getId())
                        )
                );
            } else {
                $manager = $this->getResourceManager(); /* @var $manager \TechPromux\Bundle\BaseBundle\Manager\BaseResourceTreeManager  */
                $formMapper
                        ->add('parent', null, array('class' => $this->getResourceManager()->getResourceClassShortcut(),
                            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($manager, $object) {
                                if (is_null($object) || is_null($object->getId())) {
                                    $qb = $manager->createQueryBuilder();
                                } else {
                                    $qb = $manager->createQueryBuilderExceptWithParentRoot($object->getId());
                                }
                                return $qb;
                            },
                            'choice_label' => 'levelAndNombre',
                            'required' => false,
                            "multiple" => false,
                            "expanded" => false,
                                )
                );
            }

            $formMapper->add('position', 'integer', array(
                        'required' => false,
                        'data' => $this->hasSubject() && $this->getSubject()->getPosition() ? $this->getSubject()->getPosition() : null
                    ))
                    ->end()
            ;
        }
    }

    protected function getCustomParentAssociationFieldType() {
        return 'entity';
    }

    /*
    public function getTemplate($name) {
        switch ($name) {
            case 'edit':
                return "SIPBaseBundle:Admin:ResourceTree/edit.html.twig";
        }
        return parent::getTemplate($name);
    }
    */

    public function getNewInstance() {

        $object = parent::getNewInstance(); /* @var $object \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree */
        $object->setPosition(null);
        return $object;
    }

    /**
     * @param \Sonata\CoreBundle\Validator\ErrorElement $errorElement
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function validate(\Sonata\CoreBundle\Validator\ErrorElement $errorElement, $object)
    {
        parent::validate($errorElement, $object);

        $same_code = $this->getResourceManager()->findBy(array('codigo'=>$object->getCodigo()));

        foreach($same_code as $sc)
        {
            if ($sc->getId()!=$object->getId()){
                $errorElement
                    ->with('codigo')
                    ->addViolation($this->trans('Ese valor debe ser único!',array(),'SIPBaseBundle'))
                    ->end()
                ;
            }
        }

    }

    /**
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     * @return string
     */
    public function toString($object)
    {
        return $object && $object->getNombre() ? $object->getNombre() : '';
    }

    public function getTemplate($name)
    {
        switch ($name)
        {
            case 'outer_list_rows_tree':
                return 'SIPBaseBundle:Admin:ResourceTree/list_outer_rows_tree.html.twig';
        }
        return parent::getTemplate($name); // TODO: Change the autogenerated stub
    }
}
