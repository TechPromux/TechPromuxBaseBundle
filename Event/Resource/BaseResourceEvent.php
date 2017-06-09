<?php

namespace TechPromux\Bundle\BaseBundle\Event;

abstract class BaseResourceEvent extends BaseEvent {

    protected $resource;

    public function __construct(\TechPromux\Bundle\BaseBundle\Entity\BaseResource $resource) {
        $this->resource = $resource;
    }

    /**
     * 
     * @return \TechPromux\Bundle\BaseBundle\Entity\BaseResource
     */
    public function getResource() {
        return $this->resource;
    }

}
