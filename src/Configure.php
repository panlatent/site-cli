<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli;

use Noodlehaus\Config;
use Panlatent\SiteCli\Support\Util;
use Symfony\Component\Yaml\Yaml;

/**
 * Configure
 *
 * @package Panlatent\SiteCli
 */
class Configure extends Config
{
    /**
     * Configure constructor.
     *
     * @param array|string $path
     */
    public function __construct($path)
    {
        if (is_string($path)) {
            $path = Util::realPath($path);
        } else {
            array_walk($path, function(&$value) {
               $value = Util::realPath($value);
            });
        }

        parent::__construct($path);

        $this['available'] = Util::realPath($this['available']);
        $this['enabled'] = Util::realPath($this['enabled']);
    }

    public function save(Configure $configure)
    {
        return @file_put_contents(Util::realPath('~/.site-cli.yml'), Yaml::dump($configure->all()));
    }
}