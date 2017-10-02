<?php

namespace TechPromux\BaseBundle\Admin\Resource;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use TechPromux\BaseBundle\Entity\Resource\BaseResourceTree;
use TechPromux\BaseBundle\Manager\Resource\BaseResourceTreeManager;

/**
 * Class BaseResourceTreeAdmin
 * @package  TechPromux\BaseBundle\Admin\Resource
 */
abstract class BaseResourceTreeAdmin extends BaseResourceAdmin
{

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
     * @return BaseResourceTreeManager
     */
    public function getResourceManager()
    {
        return parent::getResourceManager();
    }

    /**
     *
     * @return BaseResourceTree
     */
    public function getSubject()
    {
        return parent::getSubject();
    }

    /**
     * @param string $context
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQuery($context = 'list')
    {

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
     * @param \Sonata\AdminBundle\Route\RouteCollection $routes
     */
    public function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $routes)
    {
        parent::configureRoutes($routes);

        $routes->remove('show');
        $routes->remove('export');
        $routes->remove('batch');
        $routes->add('tree', 'tree');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

        parent::configureDatagridFilters($datagridMapper);

        $datagridMapper
            ->add('name', null, array())
            ->add('title', null, array())
            ->add('parent.name', null, array())
            ->add('parent.title', null, array());
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);

        $listMapper
            ->add('levelAndName', null, array())
            ->add('title', null, array())
            //->add('parent', null, array())
            ->add('description', null, array())
        ;

        $listMapper->add('enabled', null, array(
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
    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $object = $this->getSubject();
        /* @var $object BaseResourceTree */

        if ($object && $object->getParent() && is_null($object->getParent()->getParent())) {
            $object->setParent(null);
        }

        $formMapper
            ->with('form.label_group_general', array('class' => 'col-md-6'))
            ->add('name')
            ->add('title')
            ->add('description', 'textarea', array(
                'required' => false,
                'attr' => array('class' => 'html')
            ))
            ->add('enabled')
            ->end();

        $roots = $this->getResourceManager()->findRootChildrenElements();

        if ((is_null($object) || is_null($object->getId())) ? count($roots) > 0 : (count($roots) > 1 || (!is_null($object->getParent()) && !is_null($object->getParent()->getParent())))) {
            $formMapper
                ->with('form.label_group_options', array('class' => 'col-md-6'));

            if ($this->getCustomParentAssociationFieldType() == 'sonata_type_model_list') {
                $formMapper->add('parent', 'sonata_type_model_list', array(
                    'required' => false,
                    'btn_add' => false,
                    //'btn_delete' => false
                ), array('link_parameters' => array('exclude-parent-tree' => $this->getSubject()->getId())
                    )
                );
            } else {
                $manager = $this->getResourceManager();
                /* @var $manager \TechPromux\BaseBundle\Manager\Resource\BaseResourceTreeManager */
                $formMapper
                    ->add('parent', null, array('class' => $manager->getResourceClass(),
                            'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($manager, $object) {
                                if (is_null($object) || is_null($object->getId())) {
                                    $qb = $manager->createQueryBuilder();
                                } else {
                                    $qb = $manager->createQueryBuilderExceptWithParentRoot($object->getId());
                                }
                                return $qb;
                            },
                            'choice_label' => 'levelAndTitle',
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
                ->end();
        }
    }

    /**
     * @return string
     */
    protected function getCustomParentAssociationFieldType()
    {
        return 'entity';
    }

    /**
     * @return mixed|BaseResourceTree
     */
    public function getNewInstance()
    {
        $object = parent::getNewInstance();
        /* @var $object BaseResourceTree */
        $object->setPosition(null);
        return $object;
    }

    /**
     * @param ErrorElement $errorElement
     * @param BaseResourceTree $object
     */
    public function validate(\Sonata\CoreBundle\Validator\ErrorElement $errorElement, $object)
    {
        parent::validate($errorElement, $object);

        $objects_with_same_name = $this->getResourceManager()->findBy(array('name' => $object->getName()));

        foreach ($objects_with_same_name as $obj) {
            if ($obj->getId() != $object->getId()) {
                $errorElement
                    ->with('name')
                    ->addViolation($this->trans('Name must be unique', array(), 'SIPBaseBundle'))
                    ->end();
            }
        }

    }

    /**
     * @param BaseResourceTree $object
     * @return string
     */
    public function toString($object)
    {
        return $object && $object->getTitle() ? $object->getTitle() : ($object->getName() ? $object->getName() : '');
    }

    /**
     * @param string $name
     * @return string
     */
    public function getTemplate($name)
    {
        dump($name);

        switch ($name) {
            case 'outer_list_rows_tree':
                return $this->getResourceManager()->getBaseBundleName() . ':Admin:ResourceTree/list_outer_rows_tree.html.twig';
            case 'list':
                $request = $this->getRequest();
                $list_mode = $request->get('_list_mode', 'list');
                if ($list_mode == 'tree') {
                    return $this->getResourceManager()->getBaseBundleName() . ':Admin:ResourceTree/tree.html.twig';
                }
        }
        return parent::getTemplate($name);
    }

    public function postPersist($object)
    {
        parent::postPersist($object);

        $this->getResourceManager()->updateLftRgtForAllElements();
    }

    public function postUpdate($object)
    {
        parent::postUpdate($object);

        $this->getResourceManager()->updateLftRgtForAllElements();
    }

    public function postRemove($object)
    {
        parent::postRemove($object);

        $this->getResourceManager()->updateLftRgtForAllElements();
    }
}
