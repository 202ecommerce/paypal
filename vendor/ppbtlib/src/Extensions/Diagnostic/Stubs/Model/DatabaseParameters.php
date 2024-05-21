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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model;

class DatabaseParameters
{
    /**
     * @var bool
     */
    protected $optimize = false;

    /**
     * @var bool
     */
    protected $integrity = false;

    /**
     * @var bool
     */
    protected $allowFix = false;

    /**
     * @return bool
     */
    public function getOptimize()
    {
        return $this->optimize;
    }

    /**
     * @param bool $optimize
     * @return self
     */
    public function setOptimize($optimize)
    {
        $this->optimize = $optimize;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIntegrity()
    {
        return $this->integrity;
    }

    /**
     * @param bool $integrity
     * @return self
     */
    public function setIntegrity($integrity)
    {
        $this->integrity = $integrity;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowFix()
    {
        return $this->allowFix;
    }

    /**
     * @param bool $allowDiff
     * @return self
     */
    public function setAllowFix($allowFix)
    {
        $this->allowFix = $allowFix;
        return $this;
    }
}
