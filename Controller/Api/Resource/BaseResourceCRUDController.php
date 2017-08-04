<?php

namespace TechPromux\BaseBundle\Controller\Api\Resource;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\CollectionRepresentation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * BaseResourceCRUDController
 *
 * @author franklin
 */
abstract class BaseResourceCRUDController extends BaseResourceController {

    /**
     * List all resources.
     * 
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @FOSRest\Get("",options={"expose"=true})
     *
     * @Security("has_role('ROLE_API')")
     * 
     * @param Request               $request      the request object
     * @return View
     */
    public function listAction(Request $request) {
        return parent::listAction($request);
    }

    /**
     * Gets a resource for a given id.
     * 
     * @ApiDoc(
     *   resource = true,
     *   description = "Get a resource for a given id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the resource is not found"
     *   }
     * )
     *
     * @FOSRest\Get("/{id}",options={"expose"=true})  
     * 
     * @Security("has_role('ROLE_API')")
     * 
     * @param Request $request the request object
     * @param mixed     $id      the resource id
     *
     * @return View
     *
     * @throws NotFoundHttpException when resource not exist
     */
    public function getAction(Request $request, $id) {
        return parent::getAction($request, $id);
    }

    /**
     * Create a Resource from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new resource from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the data has errors"
     *   }
     * )
     *
     * @FOSRest\Post("",options={"expose"=true})  
     * 
     * @Security("has_role('ROLE_API')")
     * 
     * @param Request $request the request object
     *
     * @return View
     */
    public function postAction(Request $request) {
        return parent::postAction($request);
    }

    /**
     * Update a Resource from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Update a resource from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the data has errors"
     *   }
     * )
     *
     * @FOSRest\Put("/{id}",options={"expose"=true})  
     * 
     * @Security("has_role('ROLE_API')")
     * 
     * @param Request $request the request object
     *
     * @return View
     */
    public function putAction(Request $request, $id) {
        return parent::putAction($request, $id);
    }

    /**
     * Update partially a Resource from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Update partially a resource from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the data has errors"
     *   }
     * )
     *
     * @FOSRest\Patch("/{id}",options={"expose"=true})  
     *
     * @Security("has_role('ROLE_API')")
     * 
     * @param Request $request the request object
     *
     * @return View
     */
    public function patchAction(Request $request, $id) {
        return parent::patchAction($request, $id);
    }

    /**
     * Delete a resource.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete a resource.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     * @FOSRest\Delete("/{id}",options={"expose"=true})  
     *
     * @Security("has_role('ROLE_API')")
     * 
     * @param Request $request the request object
     *
     * @return View
     */
    public function deleteAction(Request $request, $id) {
        return parent::deleteAction($request, $id);
    }

    //--------------------------------------------------------------------------

    /**
     * Get quantity for all resources.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get quantity for all resources",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * 
     * @Security("has_role('ROLE_API')")
     *
     * @FOSRest\Get("/functions/total",options={"expose"=true})  
     * 
     * @return View
     */
    public function totalAction(Request $request)
    {
        $this->checkAccess('FUNCTIONS_TOTAL', null);
        $count = $this->getResourceManager()->getCountElements();
        $view = $this->view(array('total' => intval($count)), \FOS\RestBundle\Util\Codes::HTTP_OK);
        return $view;
    }

    /**
     * @return string
     */
    abstract protected function getBaseRouteName();

    /**
     * @return string
     */
    protected function getPaginatedActionRouteName()
    {
        return $this->getBaseRouteName() . '_paginated';
    }

    /**
     * List all resources paginated.

     * @ApiDoc(
     *   resource = true,
     *   description = "List all resources paginated",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @FOSRest\Get("/functions/paginated",options={"expose"=true})
     * @FOSRest\QueryParam(name="page", requirements="\d+", nullable=true, default="1", description="Page from which to start listing elements.")
     * @FOSRest\QueryParam(name="limit", requirements="\d+", nullable=true, default="32", description="How many elements to return.")
     *
     * @Security("has_role('ROLE_API')")
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return View
     */
    public function paginatedAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $this->checkAccess('FUNCTIONS_PAGINATED', null);
        $page = $paramFetcher->get('page', 1);
        $limit = $paramFetcher->get('limit', 32);
        $pager = $this->getResourceManager()->createPagerfantaPaginator($page, $limit);
        //$resources = $pager->getNbResults();
        $pagerfantaFactory = new PagerfantaFactory();
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager, new \Hateoas\Configuration\Route(
                $this->getPaginatedActionRouteName(), array(), true)
        );
        $view = $this->view($paginatedCollection, \FOS\RestBundle\Util\Codes::HTTP_OK);
       
        return $this->handleView($view);
    }

}
