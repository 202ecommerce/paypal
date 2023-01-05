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
