<?php

namespace PaypalAddons\Http\Client\Exception;

use PaypalAddons\Http\Client\Exception;

/**
 * Base exception for transfer related exceptions.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class TransferException extends \RuntimeException implements Exception
{
}
