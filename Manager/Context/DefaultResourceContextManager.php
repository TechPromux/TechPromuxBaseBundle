<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 16/05/2017
 * Time: 23:44
 */

namespace TechPromux\BaseBundle\Manager\Context;


use Doctrine\ORM\QueryBuilder;
use TechPromux\BaseBundle\Entity\Context\DefaultResourceContext;
use TechPromux\BaseBundle\Entity\Context\HasResourceContext;
use TechPromux\BaseBundle\Manager\Resource\BaseResourceManager;
use TechPromux\BaseBundle\Manager\Security\BaseSecurityManager;


class DefaultResourceContextManager extends BaseResourceManager implements BaseResourceContextManager
{
    /**
     *
     * @return string
     */
    public function getBundleName()
    {
        return $this->getBaseBundleName();
    }

    /**
     * Get entity class name
     *
     * @return class
     */
    public function getResourceClass()
    {
        return DefaultResourceContext::class;
    }

    /**
     * Get entity short name
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'DefaultUserResourceContext';
    }

    //-----------------------------------------------------------------------------------

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $context_relation_name
     * @param string $context_name
     *
     * @return QueryBuilder
     */
    public function addContextFilterToQueryBuilder($queryBuilder, $context_relation_name = 'context', $context_name = 'default')
    {
        $context = $this->findContext($context_name);

        $queryBuilder->andWhere(
            $queryBuilder->getRootAliases()[0] . '.' . $context_relation_name
            . '=' .
            $this->addNamedParameter($context_relation_name, $context->getId(), $queryBuilder, null)
        );

        return $queryBuilder;
    }

    /**
     * @param HasResourceContext $object
     * @param string $context_relation_name
     * @param string $context_name
     *
     * @return HasResourceContext
     */
    public function addContextRelationToObject($object, $context_name = 'default')
    {
        $context = $this->findContext($context_name);

        $object->setContext($context);

        return $object;
    }


    //------------------------------------------------------------------------------------------------

    /**
     * @param string $context_name
     *
     * @return ResourceContext
     * @throws \Exception
     */
    protected function findContext($context_name = 'default')
    {
        $context_full_name = strtolower($context_name);

        /* @var $context DefaultResourceContext */
        $context = $this->findOneOrNullBy(array('name' => $context_full_name));

        if (is_null($context)) {
            $context = $this->createNewInstance();
            $context->setName($context_full_name);
            $context->setTitle($context_name);
            $this->persist($context);
        }

        return $context;
    }


}