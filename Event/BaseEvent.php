<?php

namespace  TechPromux\BaseBundle\Event;

abstract class BaseEvent extends \Symfony\Component\EventDispatcher\Event {

    /**
     * Ejecución antes que otros
     */
    const PRIORIDAD_ALTA = 16;

    /**
     * Ejecución normal
     */
    const PRIORIDAD_NORMAL = 15;

    /**
     * Ejecución después que otros
     */
    const PRIORIDAD_BAJA = 14;

}
