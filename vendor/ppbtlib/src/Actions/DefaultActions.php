<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommence
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommence is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommence
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommence est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 *
 * @version   develop
 */

namespace PaypalPPBTlib\Actions;

use Translate;

/**
 * DefaultActions
 */
class DefaultActions
{
    /**
     * @var \ObjectModel
     */
    protected $modelObject;

    /**
     * Values conveyored by the classes
     *
     * @var array
     */
    protected $conveyor = [];

    /**
     * Set the modelObject
     *
     * @param \ObjectModel $modelObject
     *
     * @return $this
     */
    public function setModelObject($modelObject)
    {
        $this->modelObject = $modelObject;

        return $this;
    }

    /**
     * Set the conveyor
     *
     * @param array $conveyorData
     *
     * @return $this
     */
    public function setConveyor($conveyorData)
    {
        $this->conveyor = $conveyorData;

        return $this;
    }

    /**
     * Return data in conveyor
     *
     * @return array
     */
    public function getConveyor()
    {
        return $this->conveyor;
    }

    /**
     * Call next action call back of cross modules
     *
     * @param mixed $action Name of the actions chain
     *
     * @return bool
     */
    protected function forward($action)
    {
        if (!is_callable([$this, $action], false)) {
            echo $action . ' not defined';
            exit;
        }
        if (!call_user_func_array([$this, $action], [])) {
            return false;
        }

        return true;
    }

    /**
     * Translation function; needed so PS will properly parse the file
     *
     * @param string $string the string to translate
     * @param string $source the file with the translation; should always be the current file
     *
     * @return mixed|string
     */
    protected function l($string, $source)
    {
        return Translate::getModuleTranslation('paypal', $string, $source);
    }
}
