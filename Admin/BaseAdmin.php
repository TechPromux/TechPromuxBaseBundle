<?php

namespace  TechPromux\BaseBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

/**
 * Class BaseAdmin
 * @package  TechPromux\BaseBundle\Admin
 */
abstract class BaseAdmin extends AbstractAdmin
{
    protected $datagridValues = array(
        '_sort_by' => 'name',
        '_sort_order' => 'ASC',
    );

    /**
     *
     * @return array
     */
    public function getExportFormats()
    {
        return array(// 'json', 'xml', 'csv', 'xls'
        );
    }

    /**
     *
     * @return \Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface
     */
    public function getLabelTranslatorStrategy()
    {
        return $this->getConfigurationPool()->getContainer()->get('sonata.admin.label.strategy.underscore');
    }

    /**
     *
     * @param \Sonata\AdminBundle\Route\RouteCollection $routes
     */
    protected function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $routes)
    {
        $routes->remove('history');
        $routes->remove('batch');
        //$routes->remove('show');
        $routes->remove('export');
    }

    public function checkAccess($action, $object = null)
    {
        parent::checkAccess($action, $object);
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        return $query;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        unset($this->listModes['mosaic']);

        $listMapper->remove('batch');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {

    }

    /**
     * @param\Sonata\CoreBundle\Validator\ErrorElement $errorElement
     * @param mixed $object
     *
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        parent::validate($errorElement, $object);
    }

    public function prePersist($object)
    {
        parent::prePersist($object);
    }

    public function postPersist($object)
    {
        parent::postPersist($object);
    }

    public function preUpdate($object)
    {
        parent::preUpdate($object);
    }

    public function postUpdate($object)
    {
        parent::postUpdate($object);
    }

    public function preRemove($object)
    {
        parent::preRemove($object);
    }

    public function postRemove($object)
    {
        parent::postRemove($object);
    }

}