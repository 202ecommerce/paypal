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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\AbstractStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Interfaces\StubHandlerInterface;

abstract class AbstractStubHandler implements StubHandlerInterface
{
    /**
     * @var AbstractStub
     */
    protected $stub;

    /**
     * @param AbstractStub $stub
     */
    public function __construct(AbstractStub $stub)
    {
        $this->stub = $stub;
    }

    /**
     * @return AbstractStub
     */
    public function getStub()
    {
        return $this->stub;
    }

    public function export($download = true)
    {
        if (!$this->getStub()->isHasExport()) {
            return null;
        }

        $data = $this->handle();

        if (empty($data)) {
            return null;
        }

        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $stubName = (new \ReflectionClass($this->getStub()))->getShortName();
        $fileName = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $stubName)), '_') . '.json';

        if (!$download) {
            return [
                $fileName => $content,
            ];
        }

        header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
        header("Content-Type: application/force-download");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-Type: text/plain");

        echo $content;
    }
}
