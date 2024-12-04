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

namespace PaypalAddons\services;

use Exception;
use Throwable;

class ToolKit
{
    public function rrmdir($dir)
    {
        if (false === is_dir($dir)) {
            return false;
        }

        $objects = scandir($dir);

        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . '/' . $object)) {
                $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                continue;
            }

            $this->unlink($dir . DIRECTORY_SEPARATOR . $object);
        }

        return $this->rmdir($dir);
    }

    public function unlink($file)
    {
        if (false === is_file($file)) {
            return false;
        }

        try {
            return unlink($file);
        } catch (Exception $e) {
        } catch (Throwable $e) {
        }

        return false;
    }

    public function rmdir($dir)
    {
        if (false === is_dir($dir)) {
            return false;
        }

        try {
            return rmdir($dir);
        } catch (Exception $e) {
        } catch (Throwable $e) {
        }

        return false;
    }
}
