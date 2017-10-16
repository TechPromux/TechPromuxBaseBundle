<?php

namespace TechPromux\BaseBundle\Admin\Resource;

use Sonata\CoreBundle\Validator\ErrorElement;
use TechPromux\BaseBundle\Admin\BaseAdmin;
use TechPromux\BaseBundle\Entity\Resource\BaseResource;
use TechPromux\BaseBundle\Manager\Resource\BaseResourceManager;

/**
 * Class BaseResourceAdmin
 * @package  TechPromux\BaseBundle\Admin\Resource
 */
abstract class BaseResourceAdmin extends BaseAdmin
{
    /**
     * @var BaseResourceManager
     */
    protected $_resource_manager;

    /**
     * @param BaseResourceManager $resource_manager
     * @return BaseResourceAdmin
     */
    public function setResourceManager($resource_manager)
    {
        $this->_resource_manager = $resource_manager;
        return $this;
    }

    /**
     *
     * @return BaseResourceManager
     */
    public function getResourceManager()
    {
        return $this->_resource_manager;
    }

    /**
     *
     * @param string $action
     * @param BaseResource $object
     */
    public function checkAccess($action, $object = null)
    {
        parent::checkAccess($action, $object);

        $isChildAdmin = $this->isChild();

        if (!$isChildAdmin) {
            $this->getResourceManager()->checkAccess($action, $object);
        } else {
            $parentAdmin = $this->getParent();

            $parentId = $parentAdmin->getRequest()->get('id');

            $parentObject = $parentAdmin->getObject($parentId);

            $parentAdmin->getResourceManager()->checkAccess($action, $parentObject);

            $this->getResourceManager()->checkAccess($action, $object);

            //-----------------------------------

            $childAdmin = $this;

            $childId = $childAdmin->getRequest()->get('childId');

            $childObject = $childAdmin->getObject($childId);

            $childAdmin->getResourceManager()->checkAccess($action, $childObject);
        }
    }

    /**
     * @param string $context
     * @return \Doctrine\ORM\QueryBuilder|\Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $queryModified = $this->getResourceManager()->alterBaseQueryBuilder($query, array(), $context);
        return $queryModified ? $queryModified : $query;
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return $this->getResourceManager()->getBundleName() . ':Admin:' . $this->getResourceManager()->getResourceName() . '/edit.html.twig';
        }
        return parent::getTemplate($name);
    }

    /**
     *
     * @param \Sonata\CoreBundle\Validator\ErrorElement $errorElement
     * @param BaseResource $object
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        parent::validate($errorElement, $object);

        $errorElement
            ->addConstraint(new \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity(array('name')))
            ->with('name')
            ->assertNotBlank()
            ->assertLength(array('min' => 3))
            ->end();
    }

    /**
     *
     * @param BaseResource $object
     * @return string
     */
    public function toString($object)
    {
        if (!$object)
            return '';
        if (!empty($object->getTitle()))
            return $object->getTitle();
        return $object->getName() ? $object->getName() : '';
    }

    /**
     *
     * @param BaseResource $object
     */
    public function prePersist($object)
    {
        parent::prePersist($object);
        $this->getResourceManager()->prePersist($object);
    }

    /**
     *
     * @param BaseResource $object
     */
    public function postPersist($object)
    {
        parent::postPersist($object);
        $this->getResourceManager()->postPersist($object);
    }

    /**
     *
     * @param BaseResource $object
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);
        $this->getResourceManager()->preUpdate($object);
    }

    /**
     *
     * @param BaseResource $object
     */
    public function postUpdate($object)
    {
        parent::postUpdate($object);
        $this->getResourceManager()->postUpdate($object);
    }

    /**
     *
     * @param BaseResource $object
     */
    public function preRemove($object)
    {
        parent::preRemove($object);
        $this->getResourceManager()->preRemove($object);
    }

    /**
     *
     * @param BaseResource $object
     */
    public function postRemove($object)
    {
        parent::postRemove($object);
        $this->getResourceManager()->postRemove($object);
    }

}
