<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli;

class Application extends \Symfony\Component\Console\Application
{
    const NAME = 'site-cli';

    const VERSION = '1.2.0';

    public function __construct()
    {
        parent::__construct(static::NAME, static::VERSION);
    }

    public function all($namespace = null)
    {
        $all = parent::all($namespace);
        unset($all['default']);

        return $all;
    }
}