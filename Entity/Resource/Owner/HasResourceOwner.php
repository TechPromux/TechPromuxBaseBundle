<?php

namespace TechPromux\Bundle\BaseBundle\Entity\Resource\Owner;

/**
 * BaseOwner
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

