<?php

namespace TechPromux\BaseBundle\Entity\Context;

use Doctrine\ORM\Mapping as ORM;
use TechPromux\BaseBundle\Entity\Resource\BaseResource;

/**
 * BaseResourceContext
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class BaseResourceContext extends BaseResource
{
    public function __toString()
    {
        return $this->getTitle() ? $this->getTitle() : '';
    }
}

