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

class SecurityAdvisoryConfigModel
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $folderName;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $technical_description;

    /**
     * @var string
     */
    protected $public_link;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return SecurityAdvisoryConfigModel
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getFolderName()
    {
        return $this->folderName;
    }

    /**
     * @param string $folderName
     * @return SecurityAdvisoryConfigModel
     */
    public function setFolderName($folderName)
    {
        $this->folderName = $folderName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return SecurityAdvisoryConfigModel
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return SecurityAdvisoryConfigModel
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return SecurityAdvisoryConfigModel
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getTechnicalDescription()
    {
        return $this->technical_description;
    }

    /**
     * @param string $technical_description
     * @return SecurityAdvisoryConfigModel
     */
    public function setTechnicalDescription($technical_description)
    {
        $this->technical_description = $technical_description;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPublicLink()
    {
        return $this->public_link;
    }

    /**
     * @param string $public_link
     * @return SecurityAdvisoryConfigModel
     */
    public function setPublicLink($public_link)
    {
        $this->public_link = $public_link;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => $this->getType(),
            'folderName' => $this->getFolderName(),
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'description' => $this->getDescription(),
            'technical_description' => $this->getTechnicalDescription(),
            'public_link' => $this->getPublicLink(),
        ];
    }
}
