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
    const VERSION = '1.2.0';

    /**
     * @var \Panlatent\Container\Container
     */
    protected $container;

    /**
     * Application constructor.
     *
     * Using DefaultCommand replace default list command.
     *
     * @param \Panlatent\Container\Container $container
     */
    public function __construct($container)
    {
        parent::__construct(static::NAME, static::VERSION);
        $this->container = $container;
        $this->add(new DefaultCommand());
        $this->setDefaultCommand(DefaultCommand::NAME);
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
        unset($all[DefaultCommand::NAME]);

        return $all;
    }

    /**
     * @return \Panlatent\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}