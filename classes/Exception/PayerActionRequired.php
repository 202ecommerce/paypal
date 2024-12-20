<?php

namespace PaypalAddons\classes\Exception;

class PayerActionRequired extends \Exception
{
    protected $payerActionLink = '';

    public function __construct($payerActionLink = '', $message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->payerActionLink = (string) $payerActionLink;
    }

    public function getPayerActionLink()
    {
        return $this->payerActionLink;
    }
}
