<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 16/05/2017
 * Time: 23:44
 */

namespace TechPromux\Bundle\BaseBundle\Manager\Resource\Owner\DefaultUser;


use TechPromux\Bundle\BaseBundle\Entity\DefaultUserResourceOwner;
use TechPromux\Bundle\BaseBundle\Manager\Resource\BaseResourceManager;
use TechPromux\Bundle\BaseBundle\Manager\Resource\Owner\BaseResourceOwnerManager;


class DefaultUserResourceOwnerManager extends BaseResourceManager implements BaseResourceOwnerManager
{
    /**
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'TechPromuxBaseBundle';
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

    //---------------------------------------------------------------------------


    /**
     *
     * @return ResourceOwner
     * @throws \Exception
     */
    public function findOwnerFromUserByUserId($userid)
    {
        $user = $this->findUserById($userid);

        $owner = $this->findOneOrNullBy(array('user' => $userid));
        /* @var $owner DefaultUserResourceOwner */

        if (is_null($owner)) {
            $owner = $this->createNewInstance();
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
        $user = $this->findAuthenticatedUser();

        $owner = $this->findOwnerFromUserByUserId($user->getId());

        return $owner;
    }

}