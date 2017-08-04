<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 17/05/2017
 * Time: 00:14
 */

namespace TechPromux\BaseBundle\Manager\Context;

use Doctrine\ORM\QueryBuilder;
use TechPromux\BaseBundle\Entity\Context\HasResourceContext;


interface BaseResourceContextManager
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $property_relation_name
     * @param string $property_value_prefix
     *
     * @return QueryBuilder
     */
    public function addContextFilterToQueryBuilder($queryBuilder, $context_relation_name = 'context', $context_value_prefix = 'default');

    /**
     * @param HasResourceContext $object
     * @param string $property_relation_name
     * @param string $property_value_prefix
     *
     * @return HasResourceContext
     */
    public function addContextRelationToObject($object, $context_value_prefix = 'default');
}