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
