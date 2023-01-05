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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database;

class FixQueryModel
{
    /**
     * @var string
     */
    protected $fixQuery;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * @var int
     */
    protected $countRows = [];

    /**
     * @return string
     */
    public function getFixQuery()
    {
        return $this->fixQuery;
    }

    /**
     * @param string $fixQuery
     * @return FixQueryModel
     */
    public function setFixQuery($fixQuery)
    {
        $this->fixQuery = $fixQuery;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return FixQueryModel
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return FixQueryModel
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param array $rows
     * @return FixQueryModel
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountRows()
    {
        return $this->countRows;
    }

    /**
     * @param int $countRows
     * @return FixQueryModel
     */
    public function setCountRows($countRows)
    {
        $this->countRows = $countRows;
        return $this;
    }

    public function toArray()
    {
        return [
            'fix_query' => $this->getFixQuery(),
            'query' => $this->getQuery(),
            'headers' => $this->getHeaders(),
            'rows' => $this->getRows(),
            'countRows' => $this->getCountRows()
        ];
    }
}
