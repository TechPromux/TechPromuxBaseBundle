<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 23/07/2017
 * Time: 10:25
 */

namespace TechPromux\Bundle\BaseBundle\Manager\Security;


use FOS\UserBundle\Model\GroupManager;
use FOS\UserBundle\Model\UserManager;
use TechPromux\Bundle\BaseBundle\Manager\BaseManager;

abstract class BaseSecurityManager extends BaseManager
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
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    private $security_token_storage;

    /**
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    public function getSecurityTokenStorage()
    {
        return $this->security_token_storage;
    }

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $security_token_storage
     * @return BaseSecurityManager
     */
    public function setSecurityTokenStorage($security_token_storage)
    {
        $this->security_token_storage = $security_token_storage;
        return $this;
    }

    //-------------------------------------------------------------------------------------

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    private $security_authorization_checker;

    /**
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    public function getSecurityAuthorizationChecker()
    {
        return $this->security_authorization_checker;
    }

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $security_authorization_checker
     * @return BaseSecurityManager
     */
    public function setSecurityAuthorizationChecker($security_authorization_checker)
    {
        $this->security_authorization_checker = $security_authorization_checker;
        return $this;
    }

    //------------------------------------------------------------------------------------

    /**
     * Obtiene el usuario autenticado
     *
     * @return \Sonata\UserBundle\Entity\BaseUser
     * @throws \Exception
     */
    public function findAuthenticatedUser()
    {

        $token = $this->getSecurityTokenStorage()->getToken();

        if (null !== $token) {

            $user = $token->getUser();

            if (is_object($user) && $user instanceof \Symfony\Component\Security\Core\User\UserInterface) {
                return $user;
            } else {
                throw new \Exception();
            }
        }

        return null;
    }

    /**
     * Obtiene un usuario por su ID indicado
     *
     * @return \Sonata\UserBundle\Entity\BaseUser
     * @throws \Exception
     */
    public function findUserById($userid)
    {
        if (is_null($userid)) {
            throw new Exception('User ID must be not null');
        }
        $user = $this->getUserManager()->findUserBy(array('id' => $userid));
        if (is_null($user)) {
            throw new \Exception('User with id {' . $userid . '} was not found');
        }
        return $user;
    }

    /**
     * Obtiene el nombre del ROLE para los usuarios super admin
     *
     * @return string
     */
    protected function getRoleNameForSuperAdminUser()
    {
        return 'ROLE_SUPER_ADMIN';
    }

    /**
     * Permite conocer si el usuario autenticado es un super admin
     *
     * @return boolean
     */
    protected function isSuperAdminAuthenticated()
    {

        if ($this->getSecurityAuthorizationChecker()->isGranted($this->getRoleNameForSuperAdminUser())) {
            return true;
        }
        return false;
    }

    /**
     * Permite conocer si un usuario determinado por su ID es super admin
     *
     * @return boolean
     */
    protected function isSuperAdminByUserId($userid)
    {

        $user = $this->findUserById($userid);

        if ($this->getSecurityAuthorizationChecker()->isGranted($this->getRoleNameForSuperAdminUser(), $user) || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Permite conocer el locale del usuario autenticado
     *
     * @return string
     */
    public function getLocaleFromAuthenticatedUser()
    {

        $user = $this->findAuthenticatedUser();

        return (null !== $user && $user->getLocale()) ? $user->getLocale() : 'es';
    }

    /**
     * Permite conocer el locale de un usuario determinado pro su ID
     *
     * @return string
     * @throws \Exception
     */
    public function getLocaleFromUserByUserId($userid)
    {

        $user = $this->findUserById($userid);

        return (null !== $user && $user->getLocale()) ? $user->getLocale() : 'es';
    }

    //------------------------------------------------------------------------------

    /**
     * @var string
     */
    private $secret_app_string;

    /**
     * @return string
     */
    protected function getSecretAppString()
    {
        return $this->secret_app_string;
    }

    /**
     * @param string $secret_app_string
     * @return BaseSecurityManager
     */
    public function setSecretAppString($secret_app_string)
    {
        $this->secret_app_string = $secret_app_string;
        return $this;
    }


    /**
     * Codifica una cadena obteniendo otra reversible
     *
     * @param string $string
     * @return string
     */
    public function encodeReversibleString($string)
    {

        $salt = $this->getSecretAppString();

        $key = substr(base64_encode($salt), 0, 50);
        $iv = '12345678';
        $cc = base64_encode(json_encode(array(
            'time' => strrev('' . time()),
            'key' => $string,
            'random' => rand(10000000, 99999999)
        )));

        $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', 'cbc', '');

        mcrypt_generic_init($cipher, $key, $iv);
        $encrypted = mcrypt_generic($cipher, $cc);
        mcrypt_generic_deinit($cipher);

        return base64_encode($encrypted);
    }

    /**
     * Decodifica una cadena desde otra reversible
     *
     * @param string $string
     * @return string
     */
    public function decodeReversibleString($string)
    {

        $salt = $this->getSecretAppString();

        $key = substr(base64_encode($salt), 0, 50);
        $iv = '12345678';
        $cc = base64_decode($string);

        $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', 'cbc', '');

        mcrypt_generic_init($cipher, $key, $iv);
        $decrypted = mdecrypt_generic($cipher, $cc);
        mcrypt_generic_deinit($cipher);

        return json_decode(base64_decode($decrypted), true)['key'];
    }

    /**
     * Obtiene un hash irreversible de una cadena
     *
     * @param type $string
     * @return type
     */
    public function encodeHashOfString($string)
    {

        $salt = $this->getServiceContainer()->getParameter('secret');

        $key = substr(base64_encode($salt), 0, 30);

        $options = array(
            'cost' => 13,
            'salt' => $key
        );

        $hash = hash('sha512', password_hash($string, PASSWORD_BCRYPT, $options));

        return $hash;
    }



}