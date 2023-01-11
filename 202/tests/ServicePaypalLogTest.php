<?php
/**
 * 2007-2023 PayPal
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
 *  @author 2007-2023 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */

namespace PayPalTest;

use PaypalAddons\services\ServicePaypalLog;
use PaypalLog;

require_once dirname(__FILE__) . '/TotTestCase.php';
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'paypal/classes/PaypalLog.php';

class ServicePaypalLogTest extends \TotTestCase
{
    /** @var ServicePaypalLog*/
    protected $servicePaypalLog;

    protected function setUp()
    {
        parent::setUp();
        $this->servicePaypalLog = new ServicePaypalLog();
    }

    public function testGetPaypalOrderByLog()
    {
        $log = new \PaypalLog();
        $log->id = 1;
        $log->id_transaction = 'idTransaction';
        $log->id_order = 1;
        $paypalOrder = $this->servicePaypalLog->getPaypalOrderByLog($log);
        $this->assertTrue($paypalOrder instanceof \PaypalOrder);
    }

    /**
     * @dataProvider testGetLinkToTransactionProvider
     */
    public function testGetLinkToTransaction($paypalOrder, $log)
    {
        $serviceMock = $this->getMockBuilder(ServicePaypalLog::class)
            ->setMethods(array('getPaypalOrderByLog'))
            ->getMock();

        $serviceMock->method('getPaypalOrderByLog')->willReturn($paypalOrder);

        $link = $serviceMock->getLinkToTransaction($log);
        $this->assertTrue(is_string($link));
    }

    public function testGetLinkToTransactionProvider()
    {
        $paypalOrderValide = new \PaypalOrder();
        $paypalOrderValide->id = 1;
        $paypalOrderValide->method = 'EC';

        $paypalOrderInvalid = new \PaypalOrder();

        $logValid = new PaypalLog();
        $logValid->id = 1;
        $logValid->id_order = 1;
        $logValid->id_transaction = 'idTransaction';

        $logInvalid = new PaypalLog();

        $data = array(
            array($paypalOrderValide, $logValid),
            array($paypalOrderInvalid, $logValid),
            array($paypalOrderValide, $logInvalid),
            array($paypalOrderInvalid, $logInvalid),
        );

        return $data;
    }
}
