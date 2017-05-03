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

        return realpath($path);
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
}