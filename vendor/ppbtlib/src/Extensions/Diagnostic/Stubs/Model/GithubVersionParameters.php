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

class GithubVersionParameters
{
    /**
     * @var string
     */
    protected $repository;

    /**
     * @var string
     */
    protected $moduleVersion;

    /**
     * @var bool
     */
    protected $allowDiff = false;

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string $repository
     * @return GithubVersionParameters
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     * @param string $moduleVersion
     * @return GithubVersionParameters
     */
    public function setModuleVersion($moduleVersion)
    {
        $this->moduleVersion = $moduleVersion;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowDiff()
    {
        return $this->allowDiff;
    }

    /**
     * @param bool $allowDiff
     * @return self
     */
    public function setAllowDiff($allowDiff)
    {
        $this->allowDiff = $allowDiff;
        return $this;
    }
}
