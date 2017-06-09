<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 17/05/2017
 * Time: 00:14
 */

namespace TechPromux\Bundle\BaseBundle\Manager\Resource;

use TechPromux\Bundle\BaseBundle\Entity\Resource\BaseResourceOwner;


interface BaseResourceOwnerManager
{

    /**
     *
     * @return BaseResourceOwner
     * @throws \Exception
     */
    public function findOwnerFromUserByUserId($userid);

    /**
     *
     * @return BaseResourceOwner
     * @throws \Exception
     */
    public function findOwnerOfAuthenticatedUser();

}