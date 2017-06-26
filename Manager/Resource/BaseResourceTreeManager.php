<?php

namespace TechPromux\Bundle\BaseBundle\Manager\Resource;

/**
 * BaseResourceTreeManager define funciones básicas para todos los Managers de ResourcesTree
 *
 */
abstract class BaseResourceTreeManager extends BaseResourceManager
{

    /**
     * Devuelve un queryBuilder para obtener el árbol de un nodo raiz indicado
     *
     * @param integer $parent_id
     * @param boolean $include_parent
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function createQueryBuilderWithParentRoot($parent_id, $include_parent = true)
    {
        $query = $this->createQueryBuilder();
        return $this->alterQueryBuilderWithParentRoot($query, $parent_id, $include_parent);
    }

    /**
     * Devuelve un queryBuilder para obtener todos los elementos excepto el árbol de un nodo raiz indicado
     *
     * @param integer $parent_id
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function createQueryBuilderExceptWithParentRoot($parent_id)
    {
        $query = $this->createQueryBuilder();
        return $this->alterQueryBuilderExceptWithParentRoot($query, $parent_id);
    }

    /**
     * Modifica el queryBuilder base usado por las funcionalidades
     *
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param array $options
     * @param string $actions
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function alterBaseQuery($query, $options = array(), $action = 'list')
    {

        $query = parent::alterBaseQuery($query, $options, $action);

        $parent_tree = $this->findBaseRootElement();

        if (!is_null($parent_tree)) {
            $query->andWhere(
                $query->getRootAliases()[0] . '.lft <= ' . $this->addParameter('rgt', $parent_tree->getRgt(), $query)
                . ' AND ' . $query->getRootAliases()[0] . '.lft >= ' . $this->addParameter('lft', $parent_tree->getLft(), $query)
            );
        }

        $query->andWhere($query->getRootAliases()[0] . '.parent IS NOT NULL');
        $query->addOrderBy($query->getRootAliases()[0] . '.lft', 'ASC');

        return $query;
    }

    /**
     * Modifica una consulta para obtener solo los subordinados aun nodo raiz indicado
     *
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param integer $parent_id
     * @param boolean $include_parent
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function alterQueryBuilderWithParentRoot($query, $parent_id, $include_parent = true)
    {
        if (is_null($parent_id)) {
            return $query;
        }
        $parent = $this->find($parent_id);

        $query->andWhere(
            $query->getRootAliases()[0] . '.lft >=' . $this->addParameter('lft', $parent->getLft(), $query)
            . ' AND ' .
            $query->getRootAliases()[0] . '.lft <=' . $this->addParameter('rgt', $parent->getRgt(), $query)
        );
        if (!$include_parent) {
            $query->andWhere(
                $query->getRootAliases()[0] . '.id !=' . $this->addParameter('id', $parent_id, $query)
            );
        }
        return $query;
    }

    /**
     * Modifica una consulta para obtener todos excepto los subordinados aun nodo raiz indicado
     *
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param array $options
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function alterQueryBuilderExceptWithParentRoot($query, $parent_id)
    {
        if (is_null($parent_id)) {
            return $query;
        }
        $parent = $this->find($parent_id);
        $query->andWhere(
            $query->getRootAliases()[0] . '.lft > ' . $this->addParameter('rgt', $parent->getRgt(), $query)
            . ' OR ' . $query->getRootAliases()[0] . '.lft < ' . $this->addParameter('lft', $parent->getLft(), $query)
        );
        return $query;
    }

// -----------------------------------------------------------------------

    /**
     * Crea un elemento raiz a partir del cual se define el árbol general
     *
     * @return \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree
     */
    protected function createRootElement()
    {
        $root = $this->createNewInstance();
        $root->setCodigo('_ROOT_' . $this->getBundleName() . '_' . $this->getResourceName());
        $root->setNombre('');
        $root->setDescripcion('');
        $root->setLevel(-1);
        $root->setPosition(0);
        $root->setLft(0);
        $root->setRgt(0);
        $root->setRgt(PHP_INT_MAX);
        $root->setParent(null);
        $root->setHabilitado(true);
        //$this->persist($root); // Se pone recursivo
        parent::prePersist($root);
        $this->persistWithoutPreAndPostPersist($root);
        return $root;
    }

    /**
     * Obtiene el único nodo raiz base o null si no existe
     *
     * @return \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree
     */
    protected function findBaseRootElementOrNull()
    {
        $qb = parent::createBaseQueryBuilder();
        $qb->andWhere($qb->getRootAliases()[0] . '.parent IS NULL');
        $root = $qb->getQuery()->getResult();
        $root = $qb->getQuery()->getOneOrNullResult();
        return $root;
    }

    /**
     * Obtiene el único nodo raiz base (si no existe lo crea)
     *
     * @return \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree
     */
    protected function findBaseRootElement()
    {
        $root = $this->findBaseRootElementOrNull();
        if (is_null($root)) {
            $root = $this->createRootElement();
        }
        return $root;
    }

    /**
     * Obtiene un único nodo existente dado un codigo
     *
     * @param string $code
     * @return \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree
     */
    public function findOneByCodigo($code)
    {
        $qb = parent::createBaseQueryBuilder();
        $qb->andWhere($qb->getRootAliases()[0] . '.codigo = ' . $this->addParameter('codigo', $code, $qb));
        $root = $qb->getQuery()->getOneOrNullResult();
        return $root;
    }

    /**
     * Obtiene todos los nodos subordinados directamente a un nodo dado su ID
     *
     * @param string $parent_id
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findChildrenByParentId($parent_id)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->getRootAliases()[0] . '.parent = ' . $this->addParameter('parent', $parent_id, $qb))
            ->orderBy($qb->getRootAliases()[0] . '.position', 'ASC');
        return $qb->getQuery()->getResult();
    }

    /**
     * Obtiene todos los nodos subordinados directamente a un nodo dado su codigo
     *
     * @param string $code
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findChildrenByParentCode($code)
    {
        $parent = $this->findOneByCodigo($code);
        return $this->findChildrenByParentId($parent->getId());
    }

    /**
     * Obtiene todos los nodos del árbol de un nodo dado su ID
     *
     * @param string $parent_id
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findChildrenTreeByParentId($parent_id)
    {
        $qb = $this->createQueryBuilder();
        $qb = $this->alterQueryBuilderWithParentRoot($qb, $parent_id);
        $qb->orderBy($qb->getRootAliases()[0] . '.lft', 'ASC');
        return $qb->getQuery()->getResult();
    }

    /**
     * Obtiene todos los nodos del árbol de un nodo dado su codigo
     *
     * @param string $code
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findChildrenTreeByParentCode($code)
    {
        $parent = $this->findOneByCodigo($code);
        return $this->findChildrenByParentId($parent->getId());
    }

    /**
     * Obtiene todos los nodos excepto los del árbol de un nodo dado su ID
     *
     * @param string $parent_id
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findAllExceptTreeByParentId($parent_id)
    {
        $qb = $this->createQueryBuilder();
        $qb = $this->alterQueryBuilderExceptWithParentRoot($qb, $parent_id);
        $qb->orderBy($qb->getRootAliases()[0] . '.lft', 'ASC');
        return $qb->getQuery()->getResult();
    }

    /**
     * Obtiene todos los nodos excepto los del árbol de un nodo dado su codigo
     *
     * @param string $code
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findAllExceptTreeByParentCode($code)
    {
        $parent = $this->findOneByCodigo($code);
        return $this->findAllExceptTreeByParentId($parent->getId());
    }

    /**
     * Obtiene todos los elementos raíces (que no tienen superior directo)
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findRootElements()
    {
        $root = $this->findBaseRootElement();
        return $this->findChildrenByParentId($root->getId());
    }

// --------------------------------------------------------------------------
    /**
     * Actualiza las posiciones de los nodos de los subordinados de su superior (su hermanos)
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     * @param boolean $remove
     */
    public function updatePositionForSiblingsElements($object, $removed = false)
    {

        if (!is_null($object->getParent())) {
            $children = $this->findChildrenByParentId($object->getParent()->getId());
            $i = 1;
            $f = false;
            foreach ($children as $ch) {
                if ($removed && $ch->getId() != $object->getId()) {
                    $ch->setPosition($i);
                    $this->updateWithoutPreAndPostUpdate($ch);
                    $i++;
                } else if (!$removed) {
                    if (!$f && $ch->getPosition() >= $object->getPosition()) {
                        $f = true;
                        $object->setPosition($i);
                        if ($ch->getId() != $object->getId()) {
                            $ch->setPosition($i + 1);
                            $i++;
                        }
                    } else if ($ch->getId() != $object->getId()) {
                        $ch->setPosition($i);
                    }
                    $this->updateWithoutPreAndPostUpdate($ch);
                    $i++;
                }
            }
        }

        return true;
    }

    /**
     * Actualiza los niveles de todos los elementos
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function updateLevelForChildrenElements($object)
    {

        $tmp = new \Doctrine\Common\Collections\ArrayCollection();

        $children = $this->findChildrenByParentId($object->getId());

        foreach ($children as $ch) {
            $tmp->add($ch);
        }

        while (!$tmp->isEmpty()) {
            $d = $tmp->first();
            $tmp->removeElement($d);
            $children = $d->getChildren();
            foreach ($children as $ch) {
                $tmp->add($ch);
            }
            $parent = $d->getParent();
            $d->setLevel($parent->getLevel() + 1);
            $this->updateWithoutPreAndPostUpdate($d);
        }

    }

    /**
     * Actualiza las posiciones lft y rgt de los nodos
     *
     */
    public function updateLftRgtForAllElements()
    {
        $root = $this->findBaseRootElement();

        if (!is_null($root)) {
            $this->updateLftRgtForAllElements_Deep_Recursive_Course($root, 0);
        }
    }

    /**
     * Actualiza de forma recursiva las posiciones lft y rgt de los nodos
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     * @param integer $cont
     * @return integer
     */
    protected function updateLftRgtForAllElements_Deep_Recursive_Course($object, $cont)
    {
       // echo 'hola';exit;
        $cont++;
        $object->setLft($cont);
        $children = $this->findChildrenByParentId($object->getId());

        foreach ($children as $ch) {
            $cont = $this->updateLftRgtForAllElements_Deep_Recursive_Course($ch, $cont);
        }
        $object->setRgt($cont);
        $this->updateWithoutPreAndPostUpdate($object);
        return $cont;
    }

// ---------------------------------------------------------------------------------

    /**
     * Ejecuta las acciones antes de salvar
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function prePersist($object)
    {

        parent::prePersist($object);

        if (is_null($object->getParent())) {
            //root = $this->findBaseRootElementOrNull();
            $root = $this->findBaseRootElement();
            /* @var $root \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree */
            $object->setParent($root);
            $object->setLevel(is_null($root) ? -1 : 0);
            $object->setLft(is_null($root) ? 0 : $root->getRgt());
            $object->setRgt(is_null($root) ? 0 : $root->getRgt());
        } else {
            $parent = $object->getParent();
            /* @var $parent \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree */
            $object->setLevel($parent->getLevel() + 1);
            $object->setLft($parent->getRgt());
            $object->setRgt($parent->getRgt());
        }

        if (is_null($object->getPosition())) {
            $object->setPosition(PHP_INT_MAX);
        }
    }

    /**
     * Ejecuta las acciones despues de salvar
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function postPersist($object)
    {

        parent::postPersist($object);

        $this->updatePositionForSiblingsElements($object);

        $this->updateLftRgtForAllElements();
    }

    /**
     * Ejecuta las acciones antes de actualizar
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);
        if (is_null($object->getParent())) {
            $root = $this->findBaseRootElementOrNull();
            /* @var $root \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree */
            $object->setParent($root);
            $object->setLevel(is_null($root) ? -1 : 0);
            $object->setLft(is_null($root) ? 0 : $root->getRgt());
            $object->setRgt(is_null($root) ? 0 : $root->getRgt());
        } else {
            $parent = $object->getParent();
            /* @var $parent \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree */
            $object->setLevel($parent->getLevel() + 1);
            $object->setLft($parent->getRgt());
            $object->setRgt($parent->getRgt());
        }
        if (is_null($object->getPosition())) {
            $object->setPosition(PHP_INT_MAX);
        }
    }

    /**
     * Ejecuta las acciones despues de actualizar
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function postUpdate($object)
    {

        parent::postUpdate($object);

        $this->updatePositionForSiblingsElements($object);

        $this->updateLevelForChildrenElements($object);

        $this->updateLftRgtForAllElements();
    }

    /**
     * Ejecuta las acciones antes de eliminar
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function preRemove($object)
    {
        parent::preRemove($object);
        $this->removeElementAndChildren($object);
        $this->updatePositionForSiblingsElements($object, true);
    }

    /**
     * Dado un nodo a eliminar, elimina primeramente sus nodos subordinados
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function removeElementAndChildren($object)
    {

        $delete_stack = array();
        $tmp = new \Doctrine\Common\Collections\ArrayCollection();
        $children = $object->getChildren();
        foreach ($children as $ch) {
            $tmp->add($ch);
        }

        while (!$tmp->isEmpty()) {
            $t = $tmp->first();
            $tmp->removeElement($t);
            $children = $t->getChildren();
            foreach ($children as $ch) {
                $tmp->add($ch);
            }
            $delete_stack[] = $t;
        }
        for ($i = count($delete_stack) - 1; $i >= 0; $i--) {
            $t = $delete_stack[$i];
            $this->removeWithoutPreAndPostRemove($t);
        }
    }

    /**
     * Ejecuta las acciones despues de eliminar un elemento
     *
     * @param \TechPromux\Bundle\BaseBundle\Entity\BaseResourceTree $object
     */
    public function postRemove($object)
    {
        parent::postRemove($object);
        $this->updateLftRgtForAllElements();
    }

}
