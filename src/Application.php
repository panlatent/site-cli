<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli;

use Panlatent\SiteCli\Commands\DefaultCommand;

/**
 * Console Application
 *
 * @package Panlatent\SiteCli
 */
class Application extends \Symfony\Component\Console\Application
{
    /**
     * The console name.
     */
    const NAME = 'site-cli';

    /**
     * The console version.
     */
    const VERSION = '1.3.2';

    /**
     * Application constructor.
     *
     * Using DefaultCommand replace default list command.
     */
    public function __construct()
    {
        parent::__construct(static::NAME, static::VERSION);
        $this->add(new DefaultCommand());
    }

    /**
     * Get all commands.
     *
     * Remove DefaultCommand from result, because beautify command completion.
     *
     * @param string|null $namespace
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function all($namespace = null)
    {
        $all = parent::all($namespace);
        unset($all['list']);

        return $all;
    }
}