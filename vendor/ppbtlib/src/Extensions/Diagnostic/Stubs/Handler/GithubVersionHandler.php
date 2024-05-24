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

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\GithubVersionStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\AbstractStubHandler;
use PaypalPPBTlib\Utils\CacheStorage\CacheStorage;

class GithubVersionHandler extends AbstractStubHandler
{
    /**
     * @var GithubVersionStub
     */
    protected $stub;

    const GITHUB_REPO_URL = 'https://api.github.com/repos/%s/releases?per_page=3';

    public function handle()
    {
        $repositoryInfo = $this->getRepositoryInfo();

        return [
            'githubInfos' => $this->getStub()->getParameters()->getRepository(),
            'githubVersions' => $repositoryInfo,
            'moduleVersion' => $this->getStub()->getModule()->version,
        ];
    }

    protected function getRepositoryInfo()
    {
        if (empty($this->getStub()->getParameters()->getRepository())) {
            return [];
        }

        $url = sprintf(self::GITHUB_REPO_URL, $this->getStub()->getParameters()->getRepository());
    	$key = 'github-' . md5($url);
    	$cache = new CacheStorage();
    	$cache->setExpiry(3600);
    	if ($cache->exist($key) === true && $cache->isExpired($key) === false) {
            return $cache->get($key)['content'];
    	}
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $output = curl_exec($ch);
        if (curl_errno($ch) === true) {
            curl_close($ch);
            $cache->set($key, []);
            return [];
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == '404') {
            $cache->set($key, []);
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

    /**
     * @return GithubVersionStub
     */
    public function getStub()
    {
        return $this->stub;
    }
}
