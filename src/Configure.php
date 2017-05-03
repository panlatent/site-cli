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

/**
 * Configure
 *
 * @package Panlatent\SiteCli
 */
class Configure extends Config
{
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
}