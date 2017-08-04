<?php

namespace TechPromux\BaseBundle\Controller\Api\Resource;

use TechPromux\BaseBundle\Form\Type\Resource\BaseResourceFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use TechPromux\BaseBundle\Manager\Resource\BaseResourceManager;

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
     * Get a form type by action
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
     * Get a processed and validated form
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

        $formBuilder = $this->createFormBuilder($resource, $options);

        $formType->configureFormBuilder($formBuilder, $options);

        $form = $formBuilder->getForm();

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
