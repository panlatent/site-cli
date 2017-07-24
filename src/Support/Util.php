<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Support;

/**
 * Class Util
 *
 * @package Panlatent\SiteCli\Support
 */
class Util
{
    /**
     * Gets real path include `~` syntax.
     *
     * @param string $path
     * @return string
     */
    public static function realPath($path)
    {
        if (strncmp($path, '~', 1) === 0) {
            return realpath(static::home() . substr($path, 1));
        } elseif (strncmp($path, '?~', 2) === 0) {
            return '?' . realpath(static::home() . substr($path, 2));
        }

        return preg_match('#^\w+://#', $path) ? $path : realpath($path);
    }

    /**
     * @return string
     */
    public static function home()
    {
        return getenv('HOME');
    }

    /**
     * @return string
     */
    public static function user()
    {
        return getenv('USER');
    }

    /**
     * @return string
     */
    public static function cwd()
    {
        return getcwd();
    }

    /**
     * @return string
     */
    public static function project()
    {
        if (false !== strpos(__DIR__, 'phar://')) {
            $self = substr(__DIR__, strlen('phar://'));
            $self = substr($self, 0, strlen($self) - strlen('/src/Support'));
            $pos = strrpos($self, '/');

            return substr($self, 0, $pos);
        }

        return realpath(__DIR__ . '/../../');
    }

    public static function strConvertCamel($string)
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $string)));
    }

    /**
     * Gets array value via dot syntax.
     *
     * @param array  $arr
     * @param string $prefix
     * @return array
     */
    public static function arrayDotKeys($arr, $prefix = '')
    {
        $keys = [];
        foreach ($arr as $key => $value) {
            $keys[] = $prefix . $key;
            if (is_array($value)) {
                $keys = array_merge($keys, static::arrayDotKeys($value, $prefix . $key . '.'));
            }
        }

        return $keys;
    }
}