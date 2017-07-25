<?php
/**
 * Created by PhpStorm.
 * User: franklin
 * Date: 12/01/2017
 * Time: 0:37
 */

namespace  TechPromux\BaseBundle\Form\Resource;

use  TechPromux\BaseBundle\Manager\Resource\BaseResourceManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseResourceFormType extends AbstractType
{
    /**
     * @var BaseResourceManager
     */
    private $resource_manager;

    function __construct()
    {
        global $kernel;
        $this->resource_manager = $kernel->getContainer()->get($this->getResourceManagerID());
    }

    /**
     * @return string
     */
    protected abstract function getResourceManagerID();

    /**
     * @return BaseResourceManager
     */
    public function getResourceManager()
    {
        return $this->resource_manager;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return strtolower($this->getResourceManager()->getResourceName());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	parent::buildForm($builder, $options);
	
        $this->configureFormBuilder($builder, $options);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public abstract function configureFormBuilder(FormBuilderInterface $builder, array $options);

    /**
     * @param \Symfony\Component\Form\Form $form
     * @return \Symfony\Component\Form\Form
     */
    public abstract function validateForm(\Symfony\Component\Form\Form $form);

}