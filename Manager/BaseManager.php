<?php

namespace TechPromux\BaseBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * BaseManager
 */
abstract class BaseManager
{
    /**
     * Get Base Bundle Name
     *
     * @return string
     */
    public function getBaseBundleName()
    {
        return 'TechPromuxBaseBundle';
    }

    /**
     * Get Current Bundle Name
     *
     * @return string
     */
    abstract public function getBundleName();

    //--------------------------------------------------------------------------

    /**
     * Throw an Exception
     *
     * @param string $type
     * @param string $message
     *
     * @throws \RuntimeException
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     * @throws \Exception
     */
    public function throwException($message = '', $type = 'default')
    {
        switch ($type) {
            case 'access-denied':
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
     *
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
     * @param QueryBuilder $queryBuilder
     * @param mixed $type
     *
     * @return string
     */
    protected function addNamedParameter($name, $value, $queryBuilder, $type = null)
    {
        $bound_suffix = '_' . (microtime(true) * 10000) . '_' . random_int(1000, 9999) . '_' . count($queryBuilder->getParameters());
        $placeHolder = ":" . $name . $bound_suffix;
        $queryBuilder->setParameter(substr($placeHolder, 1), $value, $type);
        return $placeHolder;
    }

    /**
     * Get result from execution of queryBuilder
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return ArrayCollection
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
     * @param QueryBuilder $queryBuilder
     *
     * @return mixed;
     */
    protected function getOneOrNullResultFromQueryBuilder(QueryBuilder $queryBuilder)
    {
        $query = $queryBuilder->getQuery();

        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * Execute a function in an transactional context
     *
     * @param $function
     *
     * @return mixed
     */
    public function executeTransaction($function)
    {
        return $this->getEntityManager()->transactional($function);
    }

    //--------------------------------------------------------------------------

    /**
     * @var Serializer
     */
    private $jms_serializer;

    /**
     * @return Serializer
     */
    public function getJmsSerializer()
    {
        return $this->jms_serializer;
    }

    /**
     * @param Serializer $jms_serializer
     *
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
     *
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
     *
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
     * @return FormType
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
     * @return FormBuilderInterface
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
     *
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
     * @param Event $event
     *
     * @return Event
     *
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
