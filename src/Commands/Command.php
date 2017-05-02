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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    protected $checkLostSymbolicLink = true;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $io = new SymfonyStyle($input, $output);
            $this->config = new CliConfig();
            $this->config->loadConfigure();
        } catch (NotFoundException $e) {
            $io->writeln([
                '',
                "<error>{$e->getMessage()}</error>"
            ]);
            if ( ! $io->confirm('Create a .site-cli.yml file to your home?', true)) {
                throw $e;
            }

            $command = $this->getApplication()->find('config');
            $arguments = array(
                'command' => 'config',
                'target'    => 'init',
            );
            $greetInput = new ArrayInput($arguments);
            $command->run($greetInput, $output);
            $this->config->loadConfigure();
        }

        $this->manager = new ConfManager($this->config['site']['available'], $this->config['site']['enabled']);
        if ($this->checkLostSymbolicLink) {
            $this->checkLostSymbolicLink($output);
        }
    }

    private function checkLostSymbolicLink(OutputInterface $output)
    {
        if ($lostSymbolicLinkEnables = $this->manager->getLostSymbolicLinkEnables()) {
            foreach ($lostSymbolicLinkEnables as $enable) {
                $output->writeln(sprintf("<error>Warning: Symbolic link lost in \"%s\"</error>", $enable));
            }
            $output->writeln('<comment>Note:</comment> Run <info>disable</info> <comment>--clear-lost</comment> will remove them');
            $output->writeln('');
        }
    }
}