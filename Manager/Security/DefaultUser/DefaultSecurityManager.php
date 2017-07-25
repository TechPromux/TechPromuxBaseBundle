<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 23/07/2017
 * Time: 17:55
 */

namespace  TechPromux\BaseBundle\Manager\Security\DefaultUser;


use  TechPromux\BaseBundle\Manager\Security\BaseSecurityManager;

class DefaultSecurityManager extends BaseSecurityManager
{
    /**
     * @var UserManager
     */
    private $user_manager;

    /**
     * @var GroupManager
     */
    private $group_manager;

    /**
     * @return UserManager
     */
    public function getUserManager()
    {
        return $this->user_manager;
    }

    /**
     * @param UserManager $user_manager
     * @return BaseSecurityManager
     */
    public function setUserManager($user_manager)
    {
        $this->user_manager = $user_manager;
        return $this;
    }

    /**
     * @return GroupManager
     */
    public function getGroupManager()
    {
        return $this->group_manager;
    }

    /**
     * @param GroupManager $group_manager
     * @return BaseSecurityManager
     */
    public function setGroupManager($group_manager)
    {
        $this->group_manager = $group_manager;
        return $this;
    }


}