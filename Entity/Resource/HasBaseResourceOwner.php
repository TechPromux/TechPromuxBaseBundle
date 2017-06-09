<?php

namespace TechPromux\Bundle\BaseBundle\Entity\Resource;

/**
 * BaseOwner
 *
 */
interface HasBaseResourceOwner
{
    /**
     * @return BaseResourceOwner
     */
    public function getOwner();

    /**
     * @param BaseResourceOwner $owner
     * @return BaseResource
     */
    public function setOwner(BaseResourceOwner $owner = null);
}

