<?php
/*
 * Since 2007 PayPal
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author Since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\AbstractStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Interfaces\StubHandlerInterface;

abstract class AbstractStubHandler implements StubHandlerInterface
{
    /**
     * @var AbstractStub
     */
    protected $stub;

    /**
     * @param AbstractStub $stub
     */
    public function __construct(AbstractStub $stub)
    {
        $this->stub = $stub;
    }

    /**
     * @return AbstractStub
     */
    public function getStub()
    {
        return $this->stub;
    }

    public function export($download = true)
    {
        if (!$this->getStub()->isHasExport()) {
            return null;
        }

        $data = $this->handle();

        if (empty($data)) {
            return null;
        }

        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $stubName = (new \ReflectionClass($this->getStub()))->getShortName();
        $fileName = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $stubName)), '_') . '.json';

        if (!$download) {
            return [
                $fileName => $content,
            ];
        }

        header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
        header("Content-Type: application/force-download");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-Type: text/plain");

        echo $content;
    }
}
