<?php

namespace TechPromux\Bundle\BaseBundle\Manager;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Class BaseManager
 *
 * @package TechPromux\Bundle\BaseBundle\Manager
 */
abstract class BaseManager
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $service_container
     */
    protected $service_container;

    /**
     * Devuelve el Nombre del Bundle Base
     *
     * @return string
     */
    public function getCoreBundleName()
    {
        return 'TechPromuxBaseBundle';
    }

    /**
     *
     * @return string
     */
    abstract public function getBundleName();

    //--------------------------------------------------------------------------

    /**
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $service_container
     */
    public function setServiceContainer(\Symfony\Component\DependencyInjection\ContainerInterface $service_container)
    {
        $this->service_container = $service_container;
    }

    /**
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->service_container;
    }

//--------------------------------------------------------------------------

    /**
     *
     * @param string $type
     * @param string $message
     * @throws \RuntimeException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function throwException($message = '', $type = 'default')
    {

        switch ($type) {
            case 'security':
                throw new AccessDeniedException($message);
            case 'not-found':
                throw new NotFoundHttpException($message);
            case 'runtime':
                throw new \RuntimeException($message);
            default:
                throw new \Exception($message);
        }
    }

//--------------------------------------------------------------------------

    /**
     * Gets "doctrine" service
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->service_container->get('doctrine');
    }

    /**
     * Gets "doctrine.orm.default_entity_manager" service
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getDoctrineEntityManager()
    {
        return $this->service_container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Adiciona un parámetro en el $queryBuilder
     *
     * @param string $name
     * @param mixed $value
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param mixed $type
     * @return string
     */
    protected function addParameter($name, $value, $queryBuilder, $type = null)
    {
        $bound_suffix = '_' . (microtime(true) * 10000) . '_' . rand(1000, 9999) . '_' . count($queryBuilder->getParameters());
        $placeHolder = ":" . $name . $bound_suffix;
        $queryBuilder->setParameter(substr($placeHolder, 1), $value, $type);
        return $placeHolder;
    }

    /**
     * Resultados de un $queryBuilder
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getResultFromQueryBuilder(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {

        $query = $queryBuilder->getQuery();

        $result = $query->getResult();

        return $result;
    }

    /**
     * Único resultado de un $queryBuilder
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return ;
     */
    protected function getOneOrNullResultFromQueryBuilder(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {

        $query = $queryBuilder->getQuery();

        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * Comienza una transacción para un conjunto de interacciones con la base de datos
     *
     * @return type
     */
    /**
     * protected function beginTransaction() {
     * $this->getDoctrineEntityManager()->beginTransaction();
     * } */
    /**
     * Finaliza una transacción para un conjunto de interacciones con la base de datos
     *
     * @return type
     */
    /**
     * protected function endTransaction() {
     * $this->getDoctrineEntityManager()->flush();
     * $this->getDoctrineEntityManager()->commit();
     * } */

    /**
     * Cancela una transacción para un conjunto de interacciones con la base de datos
     *
     * @return type
     */
    /*
      protected function rollbackTransaction() {
      return $this->getDoctrineEntityManager()->rollback();
      } */

    public function executeTransaction($function)
    {
        return $this->getDoctrineEntityManager()->transactional($function);
    }

//--------------------------------------------------------------------------

    /**
     * Obtiene el servicio "security.token_storage"
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    protected function getSecurityTokenStorage()
    {
        return $this->service_container->get('security.token_storage');
    }

    /**
     * Obtiene el servicio "security.authorization_checker"
     *
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    protected function getSecurityAuthorizationChecker()
    {
        return $this->service_container->get('security.authorization_checker');
    }

    /**
     * Obtiene el servicio "fos_user.user_manager"
     *
     * @return \FOS\UserBundle\Model\UserManager
     */
    protected function getUserManager()
    {
        return $this->service_container->get('fos_user.user_manager');
    }

    /**
     * Obtiene el servicio "fos_user.group_manager"
     *
     * @return \FOS\UserBundle\Model\GroupManager
     */
    protected function getGroupManager()
    {
        return $this->service_container->get('fos_user.group_manager');
    }

    /**
     * Obtiene el usuario autenticado
     *
     * @return \Sonata\UserBundle\Entity\BaseUser
     * @throws \Exception
     */
    public function findAuthenticatedUser()
    {

        $token = $this->service_container->get('security.token_storage')->getToken();

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
    protected function findUserById($userid)
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

//--------------------------------------------------------------------------

    /**
     * Obtiene el servicio "templating"
     *
     * @return \Symfony\Component\Templating\EngineInterface
     */
    protected function getTemplating()
    {
        return $this->service_container->get('templating');
    }

//--------------------------------------------------------------------------

    /**
     * Obtiene el servicio "translator"
     *
     * @return \Symfony\Component\Translation\DataCollectorTranslator
     */
    protected function getTranslator()
    {
        return $this->service_container->get('translator');
    }

    /**
     * Permite traducir un $string
     *
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if ($domain == null) {
            $domain = $this->getBundleName();
        }
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

// -------------------------------------------------------------------------

    /**
     * Obtiene el servicio "mailer"
     *
     * @return \Swift_Mailer
     */
    protected function getMailer()
    {
        return $this->service_container->get('mailer');
    }

    /**
     * Permite el envio de correos electronicos
     *
     * @param array $options
     * @return int
     */
    public function sendMail(array $options)
    {

        $message = \Swift_Message::newInstance()
            ->setSubject(isset($options['subject']) ? $options['subject'] : '')
            ->setFrom(isset($options['from']) ? $options['from'] : '')
            ->setTo(isset($options['to']) ? $options['to'] : '')
            ->setBody(isset($options['body']) ? $options['body'] : '', 'text/html')
            ->addPart(isset($options['body_txt']) ? $options['body_txt'] : '', 'text/plain');
        return $this->getMailer()->send($message);
    }

//--------------------------------------------------------------------------

    /**
     * Obtiene un serializer para codificar a XML y JSON
     *
     * @return \Symfony\Component\Serializer\Serializer
     */
    protected function getDefaultSerializer()
    {

        $encoders = array(new \Symfony\Component\Serializer\Encoder\XmlEncoder(), new \Symfony\Component\Serializer\Encoder\JsonEncoder());

        $normalizers = array(new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer());

        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);

        return $serializer;
    }

    /**
     * Obtiene el servicio "jms_serializer"
     *
     * @return \JMS\Serializer\Serializer
     */
    protected function getJMSSerializer()
    {
        return $this->service_container->get('jms_serializer');
    }

    /**
     * Serializa datos usando los Serializer del sistema
     *
     * @param mixed $data
     * @param string $format
     * @param string $use_serializer
     * @return string
     */
    public function serialize($data, $format = 'json', $use_serializer = 'jms')
    {

        $serializer = ($use_serializer == 'jms' ? $this->getJMSSerializer() : $this->getDefaultSerializer());

        $jsonContent = $serializer->serialize($data, $format);

        return $jsonContent;
    }

//------------------------------------------------------------------------------

    /**
     * Codifica una cadena obteniendo otra reversible
     *
     * @param string $string
     * @return string
     */
    public function encodeReversibleString($string)
    {

        $salt = $this->getServiceContainer()->getParameter('secret');

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

        $salt = $this->getServiceContainer()->getParameter('secret');

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

//--------------------------------------------------------------------------

    /**
     * Obtiene el servicio "lexik_form_filter.query_builder_updater"
     *
     * @return \Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdater
     */
    public function getFormFilterQueryBuilderAdapter()
    {
        return $this->service_container->get('lexik_form_filter.query_builder_updater');
    }

    /**
     * Creates a $formBuilder
     *
     * @param mixed $data
     * @param array $options
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\FormType
     */
    public function createFormBuilder($data = null, array $options = array())
    {
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type = 'Symfony\Component\Form\Extension\Core\Type\FormType';
        } else {
            // not using the class name is deprecated since Symfony 2.8 and
            // is only used for backwards compatibility with older versions
            // of the Form component
            $type = 'form';
        }

        return $this->service_container->get('form.factory')->createBuilder($type, $data, $options);
    }

    /**
     * Creates a named $formBuilder
     *
     * @param string $name
     * @param null $data
     * @param array $options
     *
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    public function createNamedFormBuilder($name = 'form', $data = null, array $options = array())
    {
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type = 'Symfony\Component\Form\Extension\Core\Type\FormType';
        } else {
            // not using the class name is deprecated since Symfony 2.8 and
            // is only used for backwards compatibility with older versions
            // of the Form component
            $type = 'form';
        }

        return $this->service_container->get('form.factory')->createNamedBuilder($name, $type, $data, $options);
    }

//--------------------------------------------------------------------------

    /**
     * Obtiene el servicio "event_dispatcher"
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->getServiceContainer()->get('event_dispatcher');
    }

    /**
     * Despacha un evento al sistema
     *
     * @param string $eventName
     * @param \Symfony\Component\EventDispatcher\Event $event
     * @return \Symfony\Component\EventDispatcher\Event
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function dispatchEvent($eventName, \Symfony\Component\EventDispatcher\Event $event)
    {
        if (!$event->isPropagationStopped()) {
            $dispatcher = $this->getEventDispatcher();
            $dispatcher->dispatch($eventName, $event);
        }
        return !$event->isPropagationStopped();
    }

    /**
     * Despacha un evento transaccional al sistema
     *
     * @param string $eventName
     * @param \Symfony\Component\EventDispatcher\Event $event
     * @return \Symfony\Component\EventDispatcher\Event
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function dispatchTransactionalEvent($eventName, \Symfony\Component\EventDispatcher\Event $event)
    {

        /*
         * @var $manager BaseManager 
         */
        $manager = $this;

        try {
            $this->executeTransaction(function () use ($manager, $eventName, $event) {
                $manager->dispatchEvent($eventName, $event);
                if ($event->isPropagationStopped()) {
                    $manager->throwException('EVENT_PROPAGATION_STOPPED');
                }
            });
        } catch (\Exception $exc) {
            if ($exc->getMessage() !== 'EVENT_PROPAGATION_STOPPED') {
                throw $exc;
            }
        }

        return !$event->isPropagationStopped();
    }

//--------------------------------------------------------------------------

    /**
     * Obtiene el servicio "sonata.classification.manager.context"
     *
     * @return \Sonata\ClassificationBundle\Entity\ContextManager
     */
    protected function getClassificationContextManager()
    {
        return $this->service_container->get('sonata.classification.manager.context');
    }

    /**
     * Obtiene el servicio "sonata.classification.manager.category"
     *
     * @return \Sonata\ClassificationBundle\Entity\CategoryManager
     */
    protected function getClassificationCategoryManager()
    {
        return $this->service_container->get('sonata.classification.manager.category');
    }

    /**
     * Obtiene el servicio "sonata.media.manager.media"
     *
     * @return \Sonata\MediaBundle\Entity\MediaManager
     */
    protected function getMediaManager()
    {
        return $this->service_container->get('sonata.media.manager.media');
    }

    /**
     * Obtiene el ID utilizado por defecto para generar contextos de medias
     *
     * @return string
     */
    protected function getDefaultMediaContextId()
    {
        return 'techpromux';
    }

    /**
     * Obtiene el ID utilizado para generar contextos de medias
     *
     * @return string
     */
    public function getMediaContextId()
    {
        return $this->getDefaultMediaContextId();
    }

    /**
     * Obtiene el contexto de medias
     *
     * @return \Sonata\ClassificationBundle\Entity\BaseContext
     */
    public function getMediaContext($context_id = null)
    {

        if (is_null($context_id)) {
            $context_id = $this->getMediaContextId();
        }

        $context = $this->getClassificationContextManager()->find($context_id);

        if (is_null($context)) {
            $context = $this->getClassificationContextManager()->create();
            $context->setId($context_id);
            $context->setName($context_id);
            $context->setEnabled(true);
            $context->setCreatedAt(new \Datetime());
            $context->setUpdatedAt(new \Datetime());
            $this->getDoctrineEntityManager()->persist($context);
            $this->getDoctrineEntityManager()->flush($context);

            $defaultCategory = $this->getClassificationCategoryManager()->create();
            $defaultCategory->setContext($context);
            $defaultCategory->setName($context->getId() . '_default');
            $defaultCategory->setEnabled(true);
            $defaultCategory->setCreatedAt(new \Datetime());
            $defaultCategory->setUpdatedAt(new \Datetime());
            $defaultCategory->setSlug($context->getId() . '_default');
            $defaultCategory->setDescription($context->getId() . '_default');
            $this->getDoctrineEntityManager()->persist($defaultCategory);
            $this->getDoctrineEntityManager()->flush($defaultCategory);
        }

        return $context;
    }

}
