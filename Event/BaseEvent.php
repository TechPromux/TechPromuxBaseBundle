<?php

namespace TechPromux\BaseBundle\Event;

abstract class BaseEvent extends \Symfony\Component\EventDispatcher\Event {

    /**
     * Priority for execution before than others
     */
    const PRIORITY_HIGH = 16;

    /**
     * Priority for normal execution
     */
    const PRIORITY_NORMAL = 15;

    /**
     * Priority for execution after than others
     */
    const PRIORITY_LOW = 14;

}
