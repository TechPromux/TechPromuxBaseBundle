<?php

namespace TechPromux\BaseBundle\Event;

use TechPromux\BaseBundle\Entity\Resource\BaseResource;

abstract class BaseResourceEvent extends BaseEvent {

    protected $resource;

    public function __construct(BaseResource $resource) {
        $this->resource = $resource;
    }

    /**
     * 
     * @return BaseResource
     */
    public function getResource() {
        return $this->resource;
    }

}
