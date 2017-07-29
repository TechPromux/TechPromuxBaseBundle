<?php

namespace  TechPromux\BaseBundle\Event;

abstract class BaseResourceEvent extends BaseEvent {

    protected $resource;

    public function __construct(\TechPromux\BaseBundle\Entity\BaseResource $resource) {
        $this->resource = $resource;
    }

    /**
     * 
     * @return \TechPromux\BaseBundle\Entity\BaseResource
     */
    public function getResource() {
        return $this->resource;
    }

}
