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
