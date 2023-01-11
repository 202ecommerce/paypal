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

use PaypalAddons\services\ServicePaypalIpn;

require_once dirname(__FILE__) . '/TotTestCase.php';
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';

class ServicePaypalIpnTest extends \TotTestCase
{
    /** @var ServicePaypalIpn*/
    protected $servicePaypalIpn;

    protected function setUp()
    {
        parent::setUp();
        $this->servicePaypalIpn = new ServicePaypalIpn();
    }

    public function testExists()
    {
        $this->assertTrue(is_bool($this->servicePaypalIpn->exists('idTransaction', 'status')));
    }

    public function testGetOrdersPsByTransaction()
    {
        $this->assertTrue(is_array($this->servicePaypalIpn->getOrdersPsByTransaction('idTransaction')));
    }

    /**
     * @dataProvider testGetCartByTransactionProvider
     */
    public function testGetCartByTransaction($idCart, $result)
    {
        $serviceMock = $this->getMockBuilder(ServicePaypalIpn::class)
            ->setMethods(array('getIdCartByTransaction'))
            ->getMock();

        $serviceMock->method('getIdCartByTransaction')->willReturn($idCart);
        $cart = $serviceMock->getCartByTransaction('idTransaction');
        $this->assertTrue($cart instanceof \Cart == $result);
    }

    public function testIdCartByTransaction()
    {
        $this->assertTrue(is_int($this->servicePaypalIpn->getIdCartByTransaction('idTransaction')));
    }

    public function testGetCartByTransactionProvider()
    {
        $data = array(
            array(0, false),
            array(1, true)
        );

        return $data;
    }
}
