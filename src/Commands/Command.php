<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Configure;
use Panlatent\SiteCli\Site\Manager;
use Panlatent\SiteCli\Site\NotFoundException;
use Panlatent\SiteCli\Support\Util;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Abstract class Command
 *
 * @package Panlatent\SiteCli\Commands
 */
abstract class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Panlatent\SiteCli\Application
     */
    protected $application;

    /**
     * @var \Panlatent\Container\Container
     */
    protected $container;

    /**
     * @var \Panlatent\SiteCli\Configure
     */
    protected $configure;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    public function __construct()
    {
        parent::__construct();
    }

    final protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->application = $this->getApplication();
        $this->container = $this->application->getContainer();
        $this->io = new SymfonyStyle($input, $output);
        $this->configure = $this->container[Configure::class];
        $this->container->setService(SymfonyStyle::class, $this->io);

        if ( ! method_exists($this, 'register') ||
            ! is_callable([$this, 'register'])) {
            return;
        }

        try {
            $this->container->injectMethod($this, 'register');

            if ($this->configure['validate']['lost-symbolic-link']) {
                $this->checkLostSymbolicLink($output);
            }
        } catch (NotFoundException $e) {
            if ( ! file_exists(Util::home() . '/.site-cli.yml')) {
                $this->io->writeln([
                    '',
                    "<error>{$e->getMessage()}</error>"
                ]);
                if ( ! $this->io->confirm('Create a .site-cli.yml file to your home?', true)) {
                    throw $e;
                }

                $this->createUserConfigure($output);
            }
        }
    }

    private function createUserConfigure($output)
    {
        $command = $this->getApplication()->find('config');
        $arguments = array(
            'command' => 'init',
        );
        $greetInput = new ArrayInput($arguments);
        $command->run($greetInput, $output);
    }

    private function checkLostSymbolicLink(OutputInterface $output)
    {
        $manager = $this->container[Manager::class];
        if ($lostSymbolicLinkEnables = $manager->getLostSymbolicLinkEnables()) {
            foreach ($lostSymbolicLinkEnables as $enable) {
                $output->writeln(sprintf("<error>Warning: Symbolic link lost in \"%s\"</error>", $enable));
            }
            $output->writeln('<comment>Note:</comment> Run <info>disable</info> <comment>--clear-lost</comment> will remove them');
            $output->writeln('');
        }
    }
}