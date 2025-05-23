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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\FileIntegrityStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Diff\DiffHandler;
use PaypalPPBTlib\Utils\CacheStorage\CacheStorage;

class FileIntegrityStubHandler extends AbstractStubHandler
{
    const GIT_FILE_PATH = 'https://raw.githubusercontent.com/%s/%s/%s';

    const GITHUB_REPO_URL = 'https://api.github.com/repos/%s/releases?per_page=30';
    /**
     * @var FileIntegrityStub
     */
    protected $stub;

    public function handle()
    {
        $response = [
            'module_name' => $this->getStub()->getModule()->name,
        ];
        $respositoryInfo = $this->getRepositoryInfo();
        foreach ($respositoryInfo as $repository) {
            if (isset($repository['tag_name']) && $repository['tag_name'] == $this->getStub()->getParameters()->getModuleVersion()) {
                $differences = $this->getDifferences($repository);
                if(!empty($differences)) {
                    return array_merge($differences, $response);
                }
            }
        }
        return $response;
    }

    protected function getRepositoryInfo()
    {
        if (empty($this->getStub()->getParameters()->getRepository())) {
            return [];
        }

        $url = sprintf(self::GITHUB_REPO_URL, $this->getStub()->getParameters()->getRepository());
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
    	$key = 'github-' . md5($url);
    	$cache = new CacheStorage();
    	$cache->setExpiry(3600);
    	if ($cache->exist($key) === true && $cache->isExpired($key) === false) {
            return $cache->get($key)['content'];
    	}

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $output = curl_exec($ch);
        if (curl_errno($ch) === true) {
            curl_close($ch);
            return [];
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode < 200 || $httpcode >= 300) {
            return [];
        }

        $releases = json_decode($output, true);
        foreach ($releases as $k => $release) {
            if (empty($release['prerelease']) === false) {
                unset($releases[$k]);
            }
        }
        $cache->set($key, $releases);
        return $releases;
    }

    protected function getDifferences($respository)
    {
        if (empty($respository['assets'])) {
            return null;
        }

        foreach ($respository['assets'] as $asset) {
            if (substr($asset['name'], -4) !== '.md5') {
                continue;
            }
            $md5file = $this->getMd5fileAsArray($asset['browser_download_url']);
            if (empty($md5file)) {
                return null;
            }
            return $this->buildDifferenceArray($md5file);
        }
        return null;
    }

    protected function getMd5fileAsArray($url)
    {

    	$key = 'github-md5-' . md5($url);
    	$cache = new CacheStorage();
    	$cache->setExpiry(3600);
    	if ($cache->exist($key) === true && $cache->isExpired($key) === false) {
            return $cache->get($key)['content'];
    	}

        $md5file = file_get_contents($url);
        if (empty($md5file)) {
            $cache->set($key, null);
            return null;
        }

        $lines = preg_split("/\r\n|\r|\n/", $md5file);
        foreach ($lines as &$line) {
            $line = preg_split('/\s/', $line, -1, PREG_SPLIT_NO_EMPTY);
            if (!empty($line[1])) {
                $line[1] = str_replace('./', '', $line[1]);
            }
        }
        $cache->set($key, $lines);
        return $lines;
    }

    protected function buildDifferenceArray($md5file)
    {
        $differences = [
            'created' => [],
            'missing' => [],
            'updated' => [],
        ];

        $files = [];

        foreach ($md5file as $file) {
            if (empty($file)){
                continue;
            }
            list($md5, $fileName) = $file;
            if (empty($md5) || empty($fileName)) {
                continue;
            }

            $files[$fileName] = $md5;

            $path = _PS_MODULE_DIR_ . $this->getStub()->getModule()->name . '/' . $fileName;
            if (!file_exists($path)) {
                $differences['missing'][] = $fileName;
            } else {
                $md5local = md5_file($path);
                $allowDiff = $this->getStub()->getParameters()->getAllowDiff();
                if ($md5local !== (string) $md5) {
                    // Diff not computed
                    if ($allowDiff === false) {
                        $updated = [
                            'path' => $fileName,
                            'diff' => '',
                        ];
                        $differences['updated'][] = $updated;
                        continue;
                    }
                    $url = sprintf(
                        self::GIT_FILE_PATH,
                        $this->getStub()->getParameters()->getRepository(),
                        $this->getStub()->getParameters()->getModuleVersion(),
                        $fileName
                    );
                    $urlContent = file_get_contents($url);
                    $diffHandler = (new DiffHandler())->setModule($this->getStub()->getModule());
                    $updated = [
                        'path' => $fileName,
                        'diff' => $diffHandler->handle($urlContent, file_get_contents($path)),
                    ];
                    $differences['updated'][] = $updated;
                }
            }
        }

        $path = _PS_MODULE_DIR_ . $this->getStub()->getModule()->name;
	    $directory = new \RecursiveDirectoryIterator($path);
	    $iterator = new \RecursiveIteratorIterator($directory);
        foreach($iterator as $item) {
            $pathname = substr($item->getPathname(), strlen($path) + 1);
	        if ($item->isDir()) {
                continue;
	        }
	        if (isset($files[$pathname])) {
                continue;
	        }
            $differences['created'][] = $pathname;
	    }
        return $differences;
    }

    /**
     * @return FileIntegrityStub
     */
    public function getStub()
    {
        return $this->stub;
    }
}
