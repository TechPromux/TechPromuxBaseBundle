<?php

namespace TechPromux\Bundle\BaseBundle\Manager\Resource;

use TechPromux\Bundle\BaseBundle\Entity\BaseResource;
use TechPromux\Bundle\BaseBundle\Entity\Resource\Owner\HasResourceOwner;
use TechPromux\Bundle\BaseBundle\Manager\BaseManager;
use TechPromux\Bundle\BaseBundle\Manager\Owner\BaseResourceOwnerManager;

/**
 * BaseResourceManager define funciones básicas para todos los Managers de Resources
 *
 * @author franklin
 */
abstract class BaseResourceManager extends BaseManager
{

    /**
     * Obtiene la clase de la entidad
     *
     * @return class
     */
    abstract public function getResourceClass();

    /**
     * Obtiene el shortcut de la clase de la entidad
     *
     * @return string
     */
    public function getResourceClassShortcut()
    {
        return $this->getBundleName() . ':' . $this->getResourceName();
    }

    /**
     * Obtiene el nombre corto de la entidad
     *
     * @return string
     */
    abstract public function getResourceName();

    //--------------------------------------------------------------------------

    /**
     * @return BaseResourceOwnerManager
     */
    public function getResourceOwnerManager()
    {
        return $this->service_container->get($this->getResourceOwnerManagerID());
    }

    /**
     * @return string
     */
    public function getResourceOwnerManagerID()
    {
        return $this->getDefaultResourceOwnerManagerID();
    }

    /**
     * @return string
     */
    public function getDefaultResourceOwnerManagerID()
    {
        return 'techpromux_default.manager.owner';
    }
    //--------------------------------------------------------------------------

    /**
     * Obtiene el Entity Repository de la Entidad o de otra indicada.
     *
     * @param class $class
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getDoctrineEntityRepository($class = null)
    {
        return $this->getDoctrineEntityManager()->getRepository($class ?: $this->getResourceClassShortcut());
    }

    /**
     * Crear un $query para la Entidad
     *
     * @param class $class
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createBaseQueryBuilder($class = null)
    {
        $query = $this->getDoctrineEntityRepository($class)->createQueryBuilder('r0_' . rand(100000, 999999));
        return $query;
    }

    /**
     * Crear un $query para la Entidad añadiendo parámetros personalizados
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param class $class
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder(array $criteria = null, array $orderBy = null, $limit = null, $offset = null, $class = null)
    {
        $baseQuery = $this->createBaseQueryBuilder($class);

        $query = $this->alterBaseQuery($baseQuery);

        if (!is_null($criteria)) {
            foreach ($criteria as $key => $value) {
                $query->andWhere($query->getRootAliases()[0] . '.' . $key . ' = ' . $this->addParameter($key, $value, $query));
            }
        }

        if (!is_null($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $query->addOrderBy($query->getRootAliases()[0] . '.' . $sort, $order);
            }
        }

        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }

        if (!is_null($offset)) {
            $query->setFirstResult($offset);
        }

        return $query;
    }

    /**
     * Modificar $query base usado en las funcionalidades estandares
     *
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param array $options
     * @param string $action
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function alterBaseQuery($query, $options = array(), $action = 'list')
    {
        if ($this->getManagedResourceHasOwnerProperty()) {
            $owner = $this->getResourceOwnerManager()->findOwnerOfAuthenticatedUser();
            $parameterName = $this->getManagedResourceOwnerPropertyName();
            $query->andWhere(
                $query->getRootAliases()[0] . '.' . $parameterName
                . '=' .
                $this->addParameter($parameterName, $owner->getId(), $query, null)
            );
        }
        return $query;
    }

    protected function getManagedResourceHasOwnerProperty()
    {
        if (in_array(HasResourceOwner::class, class_implements($this->getResourceClass())))
            return true;
        return false;
    }

    protected function getManagedResourceOwnerPropertyName()
    {
        return 'owner';
    }


    // -------------------------------------------------------------------------

    /**
     * Obtiene el elemento de ID indicado o lanza un error (404 Not Found Http Exception).
     *
     * @param mixed $id
     *
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function find($id)
    {
        if (!($object = $this->getDoctrineEntityRepository()->find($id))) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $object;
    }

    /**
     * Obtiene el elemento con valores indicados o lanza un error (404 Not Found Http Exception).
     *
     * @param array $criteria
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function findOneBy(array $criteria)
    {

        $query = $this->createQueryBuilder($criteria);

        if (!($object = $this->getOneOrNullResultFromQueryBuilder($query))) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('The resource was not found.'));
        }

        return $object;
    }

    /**
     * Obtiene un único nodo existente dado un codigo
     *
     * @param string $name
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResourceTree
     */
    public function findOneByName($name)
    {
        //$qb = parent::createBaseQueryBuilder();
        $qb = $this->createBaseQueryBuilder();
        $qb->andWhere($qb->getRootAliases()[0] . '.name = ' . $this->addParameter('name', $name, $qb));
        $root = $qb->getQuery()->getOneOrNullResult();
        return $root;
    }

    /**
     * Obtiene el elemento con valores indicados o null si no existe.
     *
     * @param array $criteria
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function findOneOrNullBy(array $criteria)
    {

        $query = $this->createQueryBuilder($criteria);

        $object = $this->getOneOrNullResultFromQueryBuilder($query);

        return $object;
    }

    /**
     *
     * @param array $criteria
     * @param array $orderBy
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $query = $this->createQueryBuilder($criteria, $orderBy, $limit, $offset);
        return $this->getResultFromQueryBuilder($query);
    }

    /**
     * Obtiene todos los elementos basados en la $query por defecto.
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

    // -------------------------------------------------------------------------

    /**
     * Crea un Paginador personalizado basado en Pagerfanta.
     *
     * @param integer $page
     * @param integer $limit
     * @param array $criteria
     * @param array $orderBy
     * @return \Pagerfanta\Pagerfanta
     */
    public function createPagerfantaPaginator($page = 1, $limit = 32, array $criteria = null, array $orderBy = null)
    {
        $pager = new \Pagerfanta\Pagerfanta(new \Pagerfanta\Adapter\DoctrineORMAdapter($this->createQueryBuilder($criteria, $orderBy)));
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($limit);
        return $pager;
    }

    /**
     * Obtiene la cantidad de elementos existentes de una Entidad.
     *
     * @param array $criteria
     * @return integer
     */
    public function getCountElements(array $criteria = null)
    {
        $query = $this->createQueryBuilder($criteria);
        $query_count = $query
            ->select('COUNT(' . $query->getRootAliases()[0] . ')')->getQuery();
        return $query_count->getSingleScalarResult();
    }

    /**
     * Obtiene los elementos de una Entidad de forma paginada
     *
     * @param int $limit the limit of the result
     * @param int $offset starting from the offset
     * @param array $criteria
     * @param array $orderBy
     *
     * @return array
     */
    public function getPaginatedElements($limit = 32, $offset = 0, array $criteria = null, array $orderBy = null)
    {
        return $this->createQueryBuilder($criteria, $orderBy)->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()->execute();
    }

    //--------------------------------------------------------------------------

    /**
     * Verifica el acceso permitido a una instancia de una Entidad
     *
     * @param string $action
     * @param mixed $resource
     * @return boolean
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function checkAccess($action, $resource)
    {
        if (strtolower($action) == 'list' || strtolower($action) == 'create' || strtolower($action) == 'post' || strtolower($action) == '' || is_null($resource)) {
            return true;
        }

        if (!is_null($resource)) {
            $query = $this->createQueryBuilder();
            $query->andWhere($query->getRootAliases()[0] . '.id = ' . $this->addParameter('id', $resource, $query));
            $result = $this->getOneOrNullResultFromQueryBuilder($query);
            if ($result) {
                return true;
            } else {
                $this->throwException('Access for this entity has been denied', 'security');
            }
        }
    }

    // -------------------------------------------------------------------------

    /**
     * Obtiene una instancia nueva de la Entidad
     *
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function createNewInstance()
    {
        $entityClass = $this->getResourceClass();
        return new $entityClass();
    }

    /**
     * Salva el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @param boolean $flushed
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function persist($object, $flushed = true)
    {
        $this->prePersist($object);
        $em = $this->getDoctrineEntityManager();
        $em->persist($object);
        if ($flushed) {
            $em->flush($object);
        }
        $this->postPersist($object);
        return $object;
    }

    /**
     * Salva el elemento sin ejecutar le PrePersist ni el PostPersist
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @param boolean $flushed
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function persistWithoutPreAndPostPersist($object, $flushed = true)
    {
        $em = $this->getDoctrineEntityManager();
        $em->persist($object);
        if ($flushed) {
            $em->flush($object);
        }
        return $object;
    }

    /**
     * Acciones antes de salvar el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function prePersist($object)
    {
        if ($this->getManagedResourceHasOwnerProperty()) {
            $owner = $this->getResourceOwnerManager()->findOwnerOfAuthenticatedUser();
            $object->setOwner($owner);
        }

        $object->setCreatedAt(new \Datetime());
        $object->setUpdatedAt(new \Datetime());
        if (is_null($object->getEnabled())) {
            $object->setEnabled(true);
        }
        return $object;
    }

    /**
     * Acciones después de salvar el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function postPersist($object)
    {
        return $object;
    }

    /**
     * Actualiza el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @param boolean $flushed
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function update($object, $flushed = true)
    {
        $this->preUpdate($object, $flushed);
        $em = $this->getDoctrineEntityManager();
        $em->persist($object);
        if ($flushed) {
            $em->flush($object);
        }
        $this->postUpdate($object, $flushed);
        return $object;
    }

    /**
     * Actualiza el elemento sin ejecutar el PreUpdate o el PostUpdate
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @param boolean $flushed
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function updateWithoutPreAndPostUpdate($object, $flushed = true)
    {
        $em = $this->getDoctrineEntityManager();
        $em->persist($object);
        if ($flushed) {
            $em->flush($object);
        }
        return $object;
    }

    /**
     * Acciones antes de actualizar el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function preUpdate($object)
    {
        $object->setUpdatedAt(new \Datetime());
        return $object;
    }

    /**
     * Acciones después de salvar el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function postUpdate($object)
    {
        return $object;
    }

    /**
     * Elimina el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @param boolean $flushed
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function remove($object, $flushed = true)
    {
        $this->preRemove($object, $flushed);
        $em = $this->getDoctrineEntityManager();
        $em->remove($object);
        if ($flushed) {
            $em->flush($object);
        }
        $this->postRemove($object, $flushed);
        return $object;
    }

    /**
     * Elimina el elemento sin ejecutar el PreRemove o el PostRemove
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @param boolean $flushed
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function removeWithoutPreAndPostRemove($object, $flushed = true)
    {
        $em = $this->getDoctrineEntityManager();
        $em->remove($object);
        if ($flushed) {
            $em->flush($object);
        }
        return $object;
    }

    /**
     * Acciones antes de eliminar el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function preRemove($object)
    {
        return $object;
    }

    /**
     * Acciones después de eliminar el elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource $object
     * @return \TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResource
     */
    public function postRemove($object)
    {
        return $object;
    }

}
