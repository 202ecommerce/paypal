<?php

namespace PaypalAddons\classes\Form;

class CheckoutForm implements FormInterface
{
    /** @var \Paypal */
    protected $module;

    protected $className;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');

        $reflection = new \ReflectionClass($this);
        $this->className = $reflection->getShortName();
    }

    public function getDesciption()
    {
        // TODO: Implement getDesciption() method.
    }

    public function save($data = null)
    {
        // TODO: Implement save() method.
    }
}
