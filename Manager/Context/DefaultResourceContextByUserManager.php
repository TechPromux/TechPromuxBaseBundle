<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 16/05/2017
 * Time: 23:44
 */

namespace TechPromux\BaseBundle\Manager\Context;

use Symfony\Component\Security\Core\User\UserInterface;
use TechPromux\BaseBundle\Entity\Context\DefaultResourceContext;

use TechPromux\BaseBundle\Manager\Security\BaseSecurityManager;


class DefaultResourceContextByUserManager extends DefaultResourceContextManager
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
        return DefaultResourceContext::class;
    }

    /**
     * Get entity short name
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'DefaultResourceContext';
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
     * @return DefaultUserResourceContextManager
     */
    public function setSecurityManager($security_manager)
    {
        $this->security_manager = $security_manager;
        return $this;
    }

    //------------------------------------------------------------------------------------------------

    /**
     * @param string $context_name
     *
     * @return ResourceContext
     * @throws \Exception
     */
    protected function findContext($context_name = 'default')
    {
        $user = $this->getSecurityManager()->findAuthenticatedUser();

        $context = $this->findContextByUser($context_name, $user);

        return $context;
    }

    /**
     * @param string $context_name
     * @param UserInterface $userid
     *
     * @return DefaultResourceContext
     */
    protected function findContextByUser($context_name = 'default', $user = null)
    {
        $context_full_name = strtolower($user->getUsername()) . '_' . strtolower($context_name);

        /* @var $context DefaultResourceContext */
        $context = $this->findOneOrNullBy(array('name' => $context_full_name));

        if (is_null($context)) {
            $context = $this->createNewInstance();
            $context->setName($context_full_name);
            $context->setTitle($context_name);
            $this->persist($context);
        }

        return $context;
    }


}