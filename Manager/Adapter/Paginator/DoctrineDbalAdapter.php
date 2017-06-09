<?php

namespace TechPrommux\BaseBundle\Adapter\Paginator;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Exception\InvalidArgumentException;

/**
 * DoctrineDbalAdapter
 *
 */
class DoctrineDbalAdapter implements \Pagerfanta\Adapter\AdapterInterface {

    private $queryBuilder;
    private $count;

    /**
     * Constructor.
     *
     * @param QueryBuilder $queryBuilder              A DBAL query builder.
     * @param int     $count Count.
     */
    public function __construct(QueryBuilder $queryBuilder, $rowCount = null) {
        if ($queryBuilder->getType() !== QueryBuilder::SELECT) {
            throw new InvalidArgumentException('Only SELECT queries can be paginated.');
        }

        $this->queryBuilder = clone $queryBuilder;
        $this->count = $rowCount ? $rowCount : $this->queryBuilder->execute()->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults() {
        return (int) $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length) {
        $qb = clone $this->queryBuilder;
        $result = $qb->setMaxResults($length)
                ->setFirstResult($offset)
                ->execute();

        return $result->fetchAll();
    }

}
