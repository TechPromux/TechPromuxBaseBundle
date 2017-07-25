<?php

namespace  TechPromux\BaseBundle\Entity\Owner\DefaultUser;

use Doctrine\ORM\Mapping as ORM;
use  TechPromux\BaseBundle\Entity\Owner\ResourceOwner;
use  TechPromux\BaseBundle\Entity\Resource\BaseResource;

/**
 * BaseUserResourceOwner
 *
 * @ORM\Table(name="techpromux_base_default_user_resource_owner")
 * @ORM\Entity()
 */
class DefaultUserResourceOwner extends BaseResource implements ResourceOwner
{
    /**
     * TODO cambiar por user interface
     * @var \Sonata\UserBundle\Entity\BaseUser
     *
     * @ORM\OneToOne(targetEntity="Sonata\UserBundle\Entity\BaseUser")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @return \Sonata\UserBundle\Entity\BaseUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Sonata\UserBundle\Entity\BaseUser $user
     * @return BaseUserResourceOwner
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }


    public function __toString()
    {
        return $this->getUser() ? $this->getUser()->getUsername() : '';
    }
}

