<?php

namespace TechPromux\Bundle\BaseBundle\Admin\Resource;

use Sonata\CoreBundle\Validator\ErrorElement;
use TechPromux\Bundle\BaseBundle\Admin\BaseAdmin;
use TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource;
use TechPromux\Bundle\BaseBundle\Manager\Resource\BaseResourceManager;

/**
 * Class BaseResourceAdmin
 * @package TechPromux\Bundle\BaseBundle\Admin\Resource
 */
abstract class BaseResourceAdmin extends BaseAdmin
{

    /**
     *
     * @return string
     */
    abstract public function getResourceManagerID();

    /**
     *
     * @return BaseResourceManager
     */
    public function getResourceManager()
    {
        return $this->getConfigurationPool()->getContainer()->get($this->getResourceManagerID());
    }

    /**
     *
     * @return string
     */
    public function getTranslationDomain()
    {
        return $this->getResourceManager()->getBundleName();
    }

    /**
     *
     * @param string $action
     * @param BaseResource $object
     */
    public function checkAccess($action, $object = null)
    {
        parent::checkAccess($action, $object);
        $this->getResourceManager()->checkAccess($action, $object);
    }

    /**
     *
     * @param string $context
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $queryModified = $this->getResourceManager()->alterBaseQuery($query, array(), $context);
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
        return $object->getName() ?: '';
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
