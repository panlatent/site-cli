<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Support;

class Util
{
    /**
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
        return realpath(__DIR__ . '/../../');
    }

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