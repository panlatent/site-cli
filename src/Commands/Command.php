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
use Panlatent\SiteCli\NotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

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
        try {
            $this->config = new CliConfig();
            $this->config->loadConfigure();
        } catch (NotFoundException $e) {
            $io = new SymfonyStyle($input, $output);
            $io->writeln("<error>{$e->getMessage()}</error>");
            if ( ! $io->confirm('Create a .site.yml file to your home?', true)) {
                throw $e;
            }

            $fs = new Filesystem();
            $fs->copy($this->config->getDefaultConfigure(), $this->config->getHome() . '.site-cli.yml');
            $this->config->loadConfigure();
        }


        $this->manager = new ConfManager($this->config['site']['available'], $this->config['site']['enabled']);
        foreach ($this->manager->getLostSymbolicLinkEnables() as $enable) {
            $output->writeln('');
            $output->writeln(sprintf("<error>Warning: Symbolic link lost in \"%s\"</error>", $enable));
            $output->writeln('');
        }
    }
}