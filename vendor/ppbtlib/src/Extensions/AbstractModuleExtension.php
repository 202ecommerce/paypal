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
namespace PaypalPPBTlib\Extensions;

abstract class AbstractModuleExtension
{
    //region Fields

    public $name;

    public $module;

    public $objectModels = array();

    public $hooks = array();

    public $extensionAdminControllers = array();

    public $controllers = array();

    public $cronTasks = array(); //TODO

    //endregion

    public function __construct($module = null)
    {
        $this->module = $module;
    }

    /**
     * @param Module $module
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Do smth during the installation process
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * Do smth during the uninstall action
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * Do shmth while each initialisation of extension, must be overrided in nested classes
     * @return void
     */
    public function initExtension()
    {

    }
}