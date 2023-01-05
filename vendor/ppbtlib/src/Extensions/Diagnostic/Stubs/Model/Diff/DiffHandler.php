<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 * @version   develop
 */

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Diff;

class DiffHandler
{
    /**
     * @var \Module
     */
    protected $module;

    public function handle($from, $to)
    {
        $fromPath = $this->makeFile($from);
        $toPath = $this->makeFile($to);

        $diff = [];
        if (!is_file($fromPath) || !is_file($toPath)) {
            return '';
        }

        exec("diff -c --width=200 $fromPath $toPath", $diff);
        $this->removeFile($from);
        $this->removeFile($to);

        return empty($diff) ? '' : implode("\r", $diff);
    }

    protected function makeFile($content)
    {
        $name = md5($content);
        $path = _PS_MODULE_DIR_ . $this->getModule()->name . '/' . $name;
        file_put_contents($path, $content);
        chmod($path, 0777);

        return $path;
    }

    protected function removeFile($content)
    {
        $name = md5($content);
        @unlink(_PS_MODULE_DIR_ . $this->getModule()->name . '/' . $name);
    }

    /**
     * @return \Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param \Module $module
     * @return DiffHandler
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }
}
