<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 16/05/2017
 * Time: 23:44
 */

namespace  TechPromux\BaseBundle\Manager\Owner\DefaultUser;


use  TechPromux\BaseBundle\Entity\Owner\DefaultUser\DefaultUserResourceOwner;
use  TechPromux\BaseBundle\Manager\Owner\BaseResourceOwnerManager;
use  TechPromux\BaseBundle\Manager\Resource\BaseResourceManager;
use  TechPromux\BaseBundle\Manager\Security\BaseSecurityManager;


class DefaultUserResourceOwnerManager extends BaseResourceManager implements BaseResourceOwnerManager
{
    /**
     *
     * @return string
     */
    public function getBundleName()
    {
        return $this->getBaseBundleName();
    }

    /**
     * Get entity class name
     *
     * @return class
     */
    public function getResourceClass()
    {
        return DefaultUserResourceOwner::class;
    }

    /**
     * Get entity short name
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'DefaultUserResourceOwner';
    }

    public function getResourceClassShortcut()
    {
        return $this->getBundleName() . ':Owner\\DefaultUser\\' . $this->getResourceName();
    }
    //---------------------------------------------------------------------------

    /**
     * @var BaseSecurityManager
     */
    private $security_manager;

    /**
     * @return BaseSecurityManager
     */
    public function getSecurityManager()
    {
        return $this->security_manager;
    }

    /**
     * @param BaseSecurityManager $security_manager
     * @return DefaultUserResourceOwnerManager
     */
    public function setSecurityManager($security_manager)
    {
        $this->security_manager = $security_manager;
        return $this;
    }

    /**
     *
     * @return ResourceOwner
     * @throws \Exception
     */
    public function findOwnerFromUserByUserId($userid)
    {
        $user = $this->getSecurityManager()->findUserById($userid);

        $owner = $this->findOneOrNullBy(array('user' => $userid));
        /* @var $owner DefaultUserResourceOwner */

        if (is_null($owner)) {
            $owner = $this->createNewInstance(); // pasar el user manager
            $user = $this->findUserById($userid);
            $owner->setUser($user);
            $this->persist($owner);
        }

        return $owner;
    }

    /**
     *
     * @return ResourceOwner
     * @throws \Exception
     */
    public function findOwnerOfAuthenticatedUser()
    {
        $user = $this->getSecurityManager()->findAuthenticatedUser();

        $owner = $this->findOwnerFromUserByUserId($user->getId());

        return $owner;
    }

}