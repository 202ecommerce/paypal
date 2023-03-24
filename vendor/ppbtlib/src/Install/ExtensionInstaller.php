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

namespace PaypalPPBTlib\Install;


use \Module;
use \Db;
use \DbQuery;
use \PrestaShopDatabaseException;
use \PrestaShopException;
use \Tab;
use \Language;
use PaypalPPBTlib\Db\ObjectModelExtension;
use PaypalPPBTlib\Extensions\AbstractModuleExtension;


class ExtensionInstaller extends AbstractInstaller
{
    //region Fields

    /**
     * @var AbstractModuleExtension
     */
    protected $extension;

    //endregion

    public function __construct($module, $extension = null)
    {
        parent::__construct($module);
        $this->extension = $extension;
    }


    //region Get-Set

    /**
     * @return array
     * @throws PrestaShopException
     */
    public function getHooks()
    {
        if ($this->extension == null) {
            throw new PrestaShopException('Extension is null, can\'t get extension\'s hooks');
        }

        return $this->extension->hooks;
    }

    /**
     * @return array
     * @throws PrestaShopException
     */
    public function getAdminControllers()
    {
        if ($this->extension == null) {
            throw new PrestaShopException('Extension is null, can\'t get extension\'s admin controllers');
        }

        return $this->extension->extensionAdminControllers;
    }

    /**
     * @return array
     * @throws PrestaShopException
     */
    public function getObjectModels()
    {
        if ($this->extension == null) {
            throw new PrestaShopException('Extension is null, can\'t get extension\'s object models');
        }

        return $this->extension->objectModels;
    }

    /**
     * @return AbstractModuleExtension
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param AbstractModuleExtension $extension
     * @return ExtensionInstaller
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    public function install()
    {
        return parent::install() && $this->extension->install();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->extension->uninstall();
    }

    //endregion
}