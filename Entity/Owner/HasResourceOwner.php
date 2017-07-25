<?php

namespace  TechPromux\BaseBundle\Entity\Owner;

/**
 * BaseUserResourceOwner
 *
 */
interface HasResourceOwner
{
    /**
     * @return ResourceOwner
     */
    public function getOwner();

    /**
     * @param ResourceOwner $owner
     * @return BaseResource
     */
    public function setOwner(ResourceOwner $owner = null);
}

