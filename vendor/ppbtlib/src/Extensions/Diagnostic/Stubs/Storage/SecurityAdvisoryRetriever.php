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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Storage;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\SecurityAdvisoryConfigModel;

class SecurityAdvisoryRetriever
{
    protected static $config = null;

    /**
     * @return SecurityAdvisoryConfigModel[]
     */
    public function retrieve()
    {
        $configs = $this->getDiagnosticConfig();

        $advisories = array_map(
            function ($config) {
                $securityAdvisoryConfigModel = new SecurityAdvisoryConfigModel();
                $securityAdvisoryConfigModel->setFolderName(!empty($config['folderName']) ? $config['folderName'] : '');
                $securityAdvisoryConfigModel->setFrom(!empty($config['from']) ? $config['from'] : '');
                $securityAdvisoryConfigModel->setType(!empty($config['type']) ? $config['type'] : '');
                $securityAdvisoryConfigModel->setTo(!empty($config['to']) ? $config['to'] : '');
                $securityAdvisoryConfigModel->setDescription(!empty($config['description']) ? $config['description'] : '');
                $securityAdvisoryConfigModel->setTechnicalDescription(!empty($config['technical_description']) ? $config['technical_description'] : '');
                $securityAdvisoryConfigModel->setPublicLink(!empty($config['public_link']) ? $config['public_link'] : '');
                return $securityAdvisoryConfigModel;
            }, $configs);

        return $advisories;
    }

    protected function getDiagnosticConfig()
    {
        if (!is_null(static::$config)) {
            return static::$config;
        }
        $file = _PS_MODULE_DIR_ . '/Paypal/SecurityAdvisory.php';

        if (!file_exists($file)) {
            static::$config = [];
            return static::$config;
        }

        static::$config = include $file;
        return static::$config;
    }
}
