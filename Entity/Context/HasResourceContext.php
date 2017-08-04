<?php

namespace TechPromux\BaseBundle\Entity\Context;

/**
 * HasResourceContext
 *
 */
interface HasResourceContext
{
    /**
     * @return BaseResourceContext
     */
    public function getContext();

    /**
     * @param BaseResourceContext $context
     * @return BaseResource
     */
    public function setContext(BaseResourceContext $context = null);
}

