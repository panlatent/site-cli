<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\CliConfig;
use Panlatent\SiteCli\ConfManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var CliConfig
     */
    protected $config;

    /**
     * @var ConfManager
     */
    protected $manager;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->config = new CliConfig();
        $this->manager = new ConfManager($this->config['site']['available'], $this->config['site']['enabled']);

        foreach ($this->manager->getLostSymbolicLinkEnables() as $enable) {
            $output->writeln('');
            $output->writeln(sprintf("<error>Warning: Symbolic link lost in \"%s\"</error>", $enable));
            $output->writeln('');
        }
    }
}