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

namespace PaypalPPBTlib\Utils\CacheStorage;

use PaypalPPBTlib\Utils\CacheStorage\Exception\CacheException;

class CacheStorage
{
    //region Fields

    /**
     * Cache directory
     *
     * @var string
     */
    protected $directory = _PS_CACHE_DIR_ . 'paypal/';

    /**
     * Cache extension
     *
     * @var string
     */
    protected $extension = '.php';

    /**
     * Expired in seconds
     *
     * @var int|null
     */
    protected $expiry = null;

    //endregion

    /**
     * CacheStorage constructor.
     *
     * @throws CacheException
     */
    public function __construct()
    {
        $this->createCacheDirectoriesIfNotExists();
    }

    /**
     * Creates cache directory if there is no cache directory
     *
     * @throws CacheException
     */
    protected function createCacheDirectoriesIfNotExists()
    {
        $this->createCacheDirectory(_PS_CACHE_DIR_);
        $this->createCacheDirectory(_PS_CACHE_DIR_ . 'paypal/');
        $this->createCacheDirectory($this->directory);
    }

    /**
     * Create cache directory
     *
     * @param string $path
     *
     * @throws CacheException
     */
    protected function createCacheDirectory($path)
    {
        if (!is_dir($path)) {
            $makeDir = mkdir($path);
            if (!$makeDir) {
                throw new CacheException(sprintf('Error while creating cache directory : %s', $path));
            }
        }
    }

    /**
     * @param string|null $condition
     * Clean all files in cache directory according to condition
     *
     * @return bool
     */
    public function cleanCacheDirectory($condition = null)
    {
        if ($handle = opendir($this->directory)) {
            while (false !== ($entry = readdir($handle))) {
                if (is_file($this->directory . $entry)) {
                    if ($condition == null) {
                        unlink($this->directory . $entry);
                    } elseif (preg_match('!' . $condition . '!', $entry)) {
                        unlink($this->directory . $entry);
                    }
                }
            }

            closedir($handle);

            return true;
        }

        return false;
    }

    /**
     * @param object|string|array $key
     * Check cache exist with given key
     *
     * @return bool
     */
    public function exist($key)
    {
        return file_exists($this->getKeyFileName($key));
    }

    /**
     * @param array $params
     * Build cache file name from parameters
     *
     * @return string
     */
    public function buildKeyFromParams(array $params)
    {
        return md5(serialize($params));
    }

    /**
     * @param object|string|array $key
     * Check cache is expired
     *
     * @return bool
     */
    public function isExpired($key)
    {
        $cacheData = $this->get($key);

        if (is_null($cacheData['expiry'])) {
            return false;
        }

        $currentDateTime = date('Y-m-d H:i:s');
        if ($cacheData['expiry'] < $currentDateTime) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param object|string|array $key
     * @param object|string|array $content
     * @param array $params
     * @param array $optional
     *
     * Save content in cache by key
     *
     * @return void
     */
    public function set($key, $content, $params = [], $optional = [])
    {
        $fileName = $this->getKeyFileName($key);
        $content = $this->buildCacheContent($content, $params, $optional);
        $content = str_replace(['<?php', '<?', '?>'], '', $content);
        $filename = $fileName . uniqid('', true) . '.tmp';
        $dateGeneration = date('Y-m-d H:i:s');
        file_put_contents($filename, "<?php\r\r//Generated $dateGeneration\r\rreturn " . $content . ';', LOCK_EX);
        rename($filename, $fileName);
        chmod($fileName, 0777);
    }

    /**
     *
     * @param object|string|array $key
     *
     * Get cache content from key
     *
     * @return mixed
     */
    public function get($key)
    {
        return @include $this->getKeyFileName($key);
    }

    /**
     *
     * @param object|string|array $key
     *
     * Remove cache file by key/parameters
     */
    public function remove($key)
    {
        $key = $this->getKeyFileName($key);

        $this->cleanCacheDirectory($key);
    }

    /**
     *
     * @param object|string|array $key
     *
     * Build cache path from array or string
     *
     * @return string
     */
    protected function getKeyFileName($key)
    {
        if (is_array($key)) {
            $key = $this->buildKeyFromParams($key);
        }

        if (!(substr($key, -(strlen($this->extension))) === $this->extension)) {
            $key .= $this->extension;
        }

        return $this->directory . $key;
    }

    /**
     *
     *
     * @param object|string|array $content
     * @param array $params
     *
     * Build cache array from content (adding expiry date)
     *
     * @return string|string[]
     */
    protected function buildCacheContent($content, $params, $optional = [])
    {
        $contentWithExpiry = [
            'expiry' => is_null($this->expiry)
                ? null :
                date('Y-m-d H:i:s', strtotime(sprintf('+%d seconds', $this->expiry))),
            'content' => $content,
        ];
        if (!empty($params)) {
            $contentWithExpiry['params'] = $params;
        }
        if (!empty($optional)) {
            $contentWithExpiry['optional'] = $optional;
        }

        $contentWithExpiry = var_export($contentWithExpiry, true);

        return str_replace('stdClass::__set_state', '(object)', $contentWithExpiry);
    }

    //region Get-Set

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     *
     * @return CacheStorage
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     *
     * @return CacheStorage
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param int|null $expiry
     *
     * @return CacheStorage
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;

        return $this;
    }

    //endregion
}
