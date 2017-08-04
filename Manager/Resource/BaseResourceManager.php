<?php

namespace TechPromux\BaseBundle\Manager\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TechPromux\BaseBundle\Entity\Context\HasResourceContext;
use TechPromux\BaseBundle\Entity\Resource\BaseResource;
use TechPromux\BaseBundle\Manager\BaseManager;
use TechPromux\BaseBundle\Manager\Context\BaseResourceContextManager;

/**
 * BaseResourceManager define funciones básicas para todos los Managers de Resources
 *
 * @author franklin
 */
abstract class BaseResourceManager extends BaseManager
{

    /**
     * Get entity class name
     *
     * @return class
     */
    abstract public function getResourceClass();

    /**
     * Get entity short name
     *
     * @return string
     */
    abstract public function getResourceName();

    //--------------------------------------------------------------------------

    /**
     * @var BaseResourceContextManager
     */
    private $resource_context_manager;

    /**
     * @return BaseResourceContextManager
     */
    public function getResourceContextManager()
    {
        return $this->resource_context_manager;
    }

    /**
     * @param BaseResourceContextManager $resource_context_manager
     * @return BaseResourceManager
     */
    public function setResourceContextManager($resource_context_manager)
    {
        $this->resource_context_manager = $resource_context_manager;
        return $this;
    }

    //--------------------------------------------------------------------------

    /**
     * Get Entity Repository for an Entity Class
     *
     * @param class $class
     *
     * @return EntityRepository
     */
    protected function getDoctrineEntityRepository($class = null)
    {
        return $this->getEntityManager()->getRepository($class ?: $this->getResourceClass());
    }

    /**
     * Create an queryBuilder object for an Entity class
     *
     * @param class $class
     *
     * @return QueryBuilder
     */
    protected function createBaseQueryBuilder($class = null)
    {
        $query = $this->getDoctrineEntityRepository($class)->createQueryBuilder('r0_' . rand(100000, 999999));
        return $query;
    }

    /**
     * Create an queryBuilder object for an Entity class
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param class $class
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(array $criteria = null, array $orderBy = null, $limit = null, $offset = null, $class = null)
    {
        $baseQueryBuilder = $this->createBaseQueryBuilder($class);

        $queryBuilder = $this->alterBaseQueryBuilder($baseQueryBuilder);

        if (!is_null($criteria)) {
            foreach ($criteria as $key => $value) {
                $queryBuilder->andWhere($queryBuilder->getRootAliases()[0] . '.' . $key . ' = ' . $this->addNamedParameter($key, $value, $queryBuilder));
            }
        }

        if (!is_null($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $queryBuilder->addOrderBy($queryBuilder->getRootAliases()[0] . '.' . $sort, $order);
            }
        }

        if (!is_null($limit)) {
            $queryBuilder->setMaxResults($limit);
        }

        if (!is_null($offset)) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder;
    }

    /**
     * Modify Base Query with custom options
     *
     * @param QueryBuilder $queryBuilder
     * @param array $options
     * @param string $action
     * @return QueryBuilder
     */
    public function alterBaseQueryBuilder($queryBuilder, $options = array(), $action = 'list')
    {
        if ($this->getHasContextProperty()) {

            $queryBuilder = $this->getResourceContextManager()->addContextFilterToQueryBuilder(
                $queryBuilder,
                $this->getContextPropertyRelationName(),
                $this->getContextPropertyValueNamePrefix()
            );

        }
        return $queryBuilder;
    }

    /**
     * Get if managed entity has a context property
     *
     * @return bool
     */
    protected function getHasContextProperty()
    {
        if (in_array(HasResourceContext::class, class_implements($this->getResourceClass())))
            return true;
        return false;
    }

    /**
     * Get field name of relation context property
     *
     * @return string|void
     */
    protected function getContextPropertyRelationName()
    {
        if ($this->getHasContextProperty())
            return 'context';
        return $this->throwException('Not implemented interface [' . HasResourceContext::class . ']');
    }

    /**
     * Get field prefix value of relation context property
     *
     * @return string|void
     */
    protected function getContextPropertyValueNamePrefix()
    {
        if ($this->getHasContextProperty())
            return $this->getResourceName();
        return $this->throwException('Not implemented interface [' . HasResourceContext::class . ']');
    }

    // -------------------------------------------------------------------------

    /**
     * Get an resource by id or throw 404 Not Found Http Exception.
     *
     * @param mixed $id
     *
     * @return BaseResource
     *
     * @throws NotFoundHttpException
     */
    public function find($id)
    {
        if (!($object = $this->getDoctrineEntityRepository()->find($id))) {
            $this->throwException(sprintf('The resource \'%s\' was not found', $id), 'not-found');
        }

        return $object;
    }

    /**
     * Get an resource by an array criteria or throw 404 Not Found Http Exception.
     *
     * @param array $criteria
     * @return BaseResource
     * @throws NotFoundHttpException
     */
    public function findOneBy(array $criteria)
    {
        $query = $this->createQueryBuilder($criteria);
        if (!($object = $this->getOneOrNullResultFromQueryBuilder($query))) {
            $this->throwException('The resource was not found', 'not-found');
        }
        return $object;
    }

    /**
     * Get an resource by name.
     *
     * @param string $name
     * @return BaseResourceTree
     */
    public function findOneByName($name)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->getRootAliases()[0] . '.name = ' . $this->addNamedParameter('name', $name, $qb));
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    /**
     * OGet an resource by an array criteria
     *
     * @param array $criteria
     * @return BaseResource
     * @throws NotFoundHttpException
     */
    public function findOneOrNullBy(array $criteria)
    {

        $query = $this->createQueryBuilder($criteria);

        $object = $this->getOneOrNullResultFromQueryBuilder($query);

        return $object;
    }

    /**
     *  Find some elements by an array criteria
     *
     * @param array $criteria
     * @param array $orderBy
     * @return ArrayCollection
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $query = $this->createQueryBuilder($criteria, $orderBy, $limit, $offset);
        return $this->getResultFromQueryBuilder($query);
    }

    /**
     * Find all elements by default
     *
     * @return array
     */
    public function findAll()
    {
        $query = $this->createQueryBuilder();
        //return $this->getResultFromQueryBuilder($query);

        $cacheDriver = new \Doctrine\Common\Cache\ArrayCache();
        if (!$cacheDriver->contains('cache_id')) {
            $array = $this->getResultFromQueryBuilder($query);
            $cacheDriver->save('my_array', $array);
        } else {
            $array = $cacheDriver->fetch('my_array');
        }
        return $array;
    }

    //--------------------------------------------------------------------------

    /**
     * Verify access for an action and a resource
     *
     * @param string $action
     * @param mixed $resource
     * @return boolean
     * @throws AccessDeniedException
     */
    public function checkAccess($action, $resource)
    {
        if ((is_null($resource) || is_null($resource->getId())) && (empty($action) || in_array(strtolower($action), array('list', 'create', 'post')))) {
            return true;
        }

        if (!is_null($resource)) {
            $query = $this->createQueryBuilder();
            $query->andWhere($query->getRootAliases()[0] . '.id = ' . $this->addNamedParameter('id', $resource, $query));
            $result = $this->getOneOrNullResultFromQueryBuilder($query);
            if ($result) {
                return true;
            } else {
                $this->throwException('Access for this resource has been denied', 'access-denied');
            }
        }
    }

    // -------------------------------------------------------------------------

    /**
     * Create a new instance of managed class
     *
     * @return BaseResource
     */
    public function createNewInstance()
    {
        $entityClass = $this->getResourceClass();
        return new $entityClass();
    }

    /**
     * Persist an element
     *
     * @param BaseResource $object
     * @param boolean $flushed
     * @return BaseResource
     */
    public function persist($object, $flushed = true)
    {
        $this->prePersist($object);
        $em = $this->getEntityManager();
        $em->persist($object);
        if ($flushed) {
            $em->flush($object);
        }
        $this->postPersist($object);
        return $object;
    }

    /**
     * Persist an element without prePersist and postPersist actions
     *
     * @param BaseResource $object
     * @param boolean $flushed
     * @return BaseResource
     */
    public function persistWithoutPreAndPostPersist($object, $flushed = true)
    {
        $em = $this->getEntityManager();
        $em->persist($object);
        if ($flushed) {
            $em->flush($object);
        }
        return $object;
    }

    /**
     * Actions before persist an element
     *
     * @param BaseResource $object
     * @return BaseResource
     */
    public function prePersist($object)
    {
        if ($this->getHasContextProperty()) {

            $this->getResourceContextManager()->addContextRelationToObject(
                $object,
                $this->getContextPropertyValueNamePrefix()
            );
        }

        $object->setCreatedAt(new \Datetime());
        $object->setUpdatedAt(new \Datetime());
        if (is_null($object->getEnabled())) {
            $object->setEnabled(true);
        }
        return $object;
    }

    /**
     * Actions after persist an element
     *
     * @param BaseResource $object
     * @return BaseResource
     */
    public function postPersist($object)
    {
        return $object;
    }

    /**
     * Update an element
     *
     * @param BaseResource $object
     * @param boolean $flushed
     * @return BaseResource
     */
    public function update($object, $flushed = true)
    {
        $this->preUpdate($object, $flushed);
        $em = $this->getEntityManager();
        $em->persist($object);
        if ($flushed) {
            $em->flush($object);
        }
        $this->postUpdate($object, $flushed);
        return $object;
    }

    /**
     * Update an element without preUpdate and postUpdate actions
     *
     * @param BaseResource $object
     * @param boolean $flushed
     * @return BaseResource
     */
    public function updateWithoutPreAndPostUpdate($object, $flushed = true)
    {
        $em = $this->getEntityManager();
        $em->persist($object);
        if ($flushed) {
            $em->flush($object);
        }
        return $object;
    }

    /**
     * Actions before update an element
     *
     * @param BaseResource $object
     * @return BaseResource
     */
    public function preUpdate($object)
    {
        $object->setUpdatedAt(new \Datetime());
        return $object;
    }

    /**
     * Actions after update an element
     *
     * @param BaseResource $object
     * @return BaseResource
     */
    public function postUpdate($object)
    {
        return $object;
    }

    /**
     * Remove an element
     *
     * @param BaseResource $object
     * @param boolean $flushed
     * @return BaseResource
     */
    public function remove($object, $flushed = true)
    {
        $this->preRemove($object, $flushed);
        $em = $this->getEntityManager();
        $em->remove($object);
        if ($flushed) {
            $em->flush($object);
        }
        $this->postRemove($object, $flushed);
        return $object;
    }

    /**
     * Remove an element without preRemove and postRemove actions
     *
     * @param BaseResource $object
     * @param boolean $flushed
     * @return BaseResource
     */
    public function removeWithoutPreAndPostRemove($object, $flushed = true)
    {
        $em = $this->getEntityManager();
        $em->remove($object);
        if ($flushed) {
            $em->flush($object);
        }
        return $object;
    }

    /**
     * Actions before remove an element
     *
     * @param BaseResource $object
     * @return BaseResource
     */
    public function preRemove($object)
    {
        return $object;
    }

    /**
     * Actions after remove an element
     *
     * @param BaseResource $object
     * @return BaseResource
     */
    public function postRemove($object)
    {
        return $object;
    }

}
