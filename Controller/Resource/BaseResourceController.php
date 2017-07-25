<?php

namespace  TechPromux\BaseBundle\Controller\Resource;

use  TechPromux\BaseBundle\Form\Resource\BaseResourceFormType;
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
use  TechPromux\BaseBundle\Manager\Resource\BaseResourceManager;

/**
 * BaseResourceController
 *
 * @autor franklin
 */
abstract class BaseResourceController extends FOSRestController
{

    /**
     * @return BaseResourceManager
     */
    abstract protected function getResourceManager();

    /**
     * Devuelve el formulario necesario según una action indicada
     *
     * @param string $action
     * @param mixed $object
     * @param array $options
     *
     * @return BaseResourceFormType
     */
    public function getResourceFormType($action = "POST", $resource = array(), $options = array())
{
    $this->getResourceManager()->throwException('Not implemented');
}

    /**
     * Crea, procesa y valida el formulario de una action indicada
     *
     * @param Request $request
     * @param string $action
     * @param array $resource
     * @param array $options
     * @return \Symfony\Component\Form\Form
     */
    protected function getProcessedForm(Request $request, $action = 'POST', $resource = array(), $options = array())
    {
        $formType = $this->getResourceFormType($action, $resource, $options);

        //@deprecated
        //$form = $this->createForm($formType, $resource);

        $formBuilder = $this->createFormBuilder($resource, $options);

        $formType->configureFormBuilder($formBuilder, $options);

        $form = $formBuilder->getForm();

        /*
	    $format = $request->getRequestFormat() ?: $request->get('_format', 'json');

        if ('html' === $format) {
            $form->handleRequest($request);
        } else {
            $form->submit($request, 'PATCH' !== $action);
        }
	    */

        $form->handleRequest($request);

        $formType->validateForm($form);

        return $form;
    }

    /**
     * Processes the form.
     *
     * @param Request $request
     * @param mixed $resource
     * @param string $method
     *
     * @return Resource
     */
    protected function processAction(Request $request, $action = "POST", $resource = array())
    {
        $form = $this->processForm($request, $action, $resource);

        if ($form->isValid()) {
            $resource = $form->getData();

            $this->getResourceManager()->persist($resource);

            $view = $this->view($resource, \FOS\RestBundle\Util\Codes::HTTP_OK);

            return $this->handleView($view);
        }

        $view = $this->view($form, \FOS\RestBundle\Util\Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Create a new Resource.
     *
     * @param Request $request
     *
     * @return Resource
     */
    protected function processPost(Request $request)
    {
        $resource = $this->getResourceManager()->createNewInstance();
        return $this->processAction($request, 'POST', $resource);
    }

    /**
     * Edit a Resource.
     *
     * @param Request $request
     * @param mixed $resource
     *
     * @return Resource
     */
    protected function processPut(Request $request, $resource)
    {
        return $this->processAction($request, 'PUT', $resource);
    }

    /**
     * Partially update a resource.
     *
     * @param Request $request
     * @param mixed $resource
     *
     * @return Resource
     */
    protected function processPatch(Request $request, $resource)
    {
        return $this->processAction($request, 'PATCH', $resource);
    }

    /**
     * @param string $action
     * @param mixed $resource
     */
    protected function checkAccess($action, $resource = null)
    {
        /*
         *
          $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'You must be authenticated fully');

         if (in_array($this->get('kernel')->getEnvironment(), array('test', 'dev'))) {
             $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null);
             return true;
         }
        */

        // ROLE_SIP_COMUN_API_NOMENCLADOR_TIPO_LIST
        /*
            $resourceName = $this->getResourceManager()->getResourceName();
            $role = 'ROLE_' . strtoupper($this->getSnakeBundleName()) . '_API_' . strtoupper($this->getResourceManager()->getResourceName()) . '_' . strtoupper($action);
            $this->denyAccessUnlessGranted($role, null, 'You must be authorized for action ' . strtoupper($action) . ' of resource ' . strtoupper($resourceName) . ' (' . $role . ')');
          */
        $this->getResourceManager()->checkAccess($action, $resource);
    }

//----------------------------------------------------------------------------

    /**
     * List all resources.
     *
     * @param Request $request the request object
     * @return View
     */
    public function listAction(Request $request)
    {
        $this->checkAccess('LIST', null);
        $resources = $this->getResourceManager()->findAll();
        $view = $this->view($resources, \FOS\RestBundle\Util\Codes::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Gets a resource for a given id.
     *
     * @param Request $request the request object
     * @param mixed $id the resource id
     *
     * @return View
     *
     * @throws NotFoundHttpException when resource not exist
     */
    public function getAction(Request $request, $id)
    {
        $this->checkAccess('GET', $id);
        $resource = $this->getResourceManager()->find($id);
        $view = $this->view($resource, \FOS\RestBundle\Util\Codes::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Create a Resource from the submitted data.
     *
     * @param Request $request the request object
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->checkAccess('POST', null);
        return $this->processPost($request);
    }

    /**
     * Update a Resource from the submitted data.
     *
     * @param Request $request the request object
     * @param mixed $id the resource id
     *
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $this->checkAccess('PUT', $id);
        $resource = $this->getResourceManager()->find($id);
        return $this->processPut($request, $resource);
    }

    /**
     * Update partially a Resource from the submitted data.
     *
     * @param Request $request the request object
     * @param mixed $id the resource id
     *
     * @return View
     */
    public function patchAction(Request $request, $id)
    {
        $this->checkAccess('PATCH', $id);
        $resource = $this->getResourceManager()->find($id);
        return $this->processPatch($request, $resource);
    }

    /**
     * Delete a resource.
     *
     * @param Request $request the request object
     * @param mixed $id the resource id
     *
     * @return View
     */
    public function deleteAction(Request $request, $id)
    {
        $this->checkAccess('DELETE', $id);
        $resource = $this->getResourceManager()->find($id);
        $this->getResourceManager()->remove($resource);
        $view = $this->view(null, \FOS\RestBundle\Util\Codes::HTTP_OK);
        return $this->handleView($view);
    }


}
