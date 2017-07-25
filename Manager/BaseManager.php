<?php

namespace  TechPromux\BaseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Class BaseManager
 *
 * @package  TechPromux\BaseBundle\Manager
 */
abstract class BaseManager
{
    /**
     * Devuelve el Nombre del Bundle Base
     *
     * @return string
     */
    public function getBaseBundleName()
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

    //------------------------------------------------------------------------------------------------

    /**
     * @var EntityManagerInterface
     */
    private $entity_manager;

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    /**
     * @param EntityManagerInterface $entity_manager
     * @return BaseManager
     */
    public function setEntityManager($entity_manager)
    {
        $this->entity_manager = $entity_manager;
        return $this;
    }

    //-------------------------------------------------------------------------------------------------

    /**
     * Add a new parameter to queryBuilder
     *
     * @param string $name
     * @param mixed $value
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param mixed $type
     * @return string
     */
    protected function addNamedParameter($name, $value, $queryBuilder, $type = null)
    {
        $bound_suffix = '_' . (microtime(true) * 10000) . '_' . rand(1000, 9999) . '_' . count($queryBuilder->getParameters());
        $placeHolder = ":" . $name . $bound_suffix;
        $queryBuilder->setParameter(substr($placeHolder, 1), $value, $type);
        return $placeHolder;
    }

    /**
     * Get result from execution of queryBuilder
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
     * Get one result from execution of queryBuilder
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


    public function executeTransaction($function)
    {
        return $this->getEntityManager()->transactional($function);
    }

    //--------------------------------------------------------------------------

    /**
     * @var \JMS\Serializer\Serializer
     */
    private $jms_serializer;

    /**
     * @return \JMS\Serializer\Serializer
     */
    public function getJmsSerializer()
    {
        return $this->jms_serializer;
    }

    /**
     * @param \JMS\Serializer\Serializer $jms_serializer
     * @return BaseManager
     */
    public function setJmsSerializer($jms_serializer)
    {
        $this->jms_serializer = $jms_serializer;
        return $this;
    }

    /**
     * Serialize data
     *
     * @param mixed $data
     * @param string $format
     * @return string
     */
    public function serialize($data, $format = 'json')
    {
        $jsonContent = $this->getJmsSerializer()->serialize($data, $format);

        return $jsonContent;
    }

    //--------------------------------------------------------------------------

    /**
     * @var FormFactory
     */
    private $form_factory;

    /**
     * @return FormFactory
     */
    public function getFormFactory()
    {
        return $this->form_factory;
    }

    /**
     * @param FormFactory $form_factory
     * @return BaseManager
     */
    public function setFormFactory($form_factory)
    {
        $this->form_factory = $form_factory;
        return $this;
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
        return $this->getFormFactory()->createBuilder(FormType::class, $data, $options);
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
        return $this->getFormFactory()->createNamedBuilder($name, FormType::class, $data, $options);
    }

    //--------------------------------------------------------------------------

    /**
     * @var EventDispatcher
     */
    private $event_dispatcher;

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->event_dispatcher;
    }

    /**
     * @param EventDispatcher $event_dispatcher
     * @return BaseManager
     */
    public function setEventDispatcher($event_dispatcher)
    {
        $this->event_dispatcher = $event_dispatcher;
        return $this;
    }


    /**
     * Dispatch an event
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

    //-------------------------------------------------------------------------------


}
