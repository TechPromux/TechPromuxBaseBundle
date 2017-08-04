<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 23/07/2017
 * Time: 10:25
 */

namespace TechPromux\BaseBundle\Manager\Security;


use FOS\UserBundle\Model\GroupManager;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use TechPromux\BaseBundle\Manager\BaseManager;

/**
 * Class BaseSecurityManager
 * @package TechPromux\BaseBundle\Manager\Security
 */
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
     * @var TokenStorage
     */
    private $security_token_storage;

    /**
     * @return TokenStorage
     */
    public function getSecurityTokenStorage()
    {
        return $this->security_token_storage;
    }

    /**
     * @param TokenStorage $security_token_storage
     * @return BaseSecurityManager
     */
    public function setSecurityTokenStorage($security_token_storage)
    {
        $this->security_token_storage = $security_token_storage;
        return $this;
    }

    //-------------------------------------------------------------------------------------

    /**
     * @var AuthorizationChecker
     */
    private $security_authorization_checker;

    /**
     * @return AuthorizationChecker
     */
    public function getSecurityAuthorizationChecker()
    {
        return $this->security_authorization_checker;
    }

    /**
     * @param AuthorizationChecker $security_authorization_checker
     * @return BaseSecurityManager
     */
    public function setSecurityAuthorizationChecker($security_authorization_checker)
    {
        $this->security_authorization_checker = $security_authorization_checker;
        return $this;
    }

    //------------------------------------------------------------------------------------

    /**
     * Get authenticated user
     *
     * @return UserInterface
     * @throws \Exception
     */
    public function findAuthenticatedUser()
    {

        $token = $this->getSecurityTokenStorage()->getToken();

        if (null !== $token) {

            $user = $token->getUser();

            if (is_object($user) && $user instanceof UserInterface) {
                return $user;
            }

        }
        return null;
    }

    /**
     * Get role name for Super Admin Users
     *
     * @return string
     */
    protected function getRoleNameForSuperAdminUser()
    {
        return 'ROLE_SUPER_ADMIN';
    }

    /**
     * Get if authenticated user is superadmin
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
     * Get locale from authenticated user
     *
     * @return string
     */
    public function getLocaleFromAuthenticatedUser()
    {

        $user = $this->findAuthenticatedUser();

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
     *
     * @return BaseSecurityManager
     */
    public function setSecretAppString($secret_app_string)
    {
        $this->secret_app_string = $secret_app_string;
        return $this;
    }


    /**
     * Encode an string with a reversible algorithm
     *
     * @param string $string
     *
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
     * Decode an string with a reversible algorithm
     *
     * @param string $string
     *
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
     * Encode an string with in a irreversible hash
     *
     * @param type $string
     *
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