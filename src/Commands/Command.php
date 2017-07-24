<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Exception;
use Panlatent\SiteCli\Configure;
use Panlatent\SiteCli\Service\Reloadable;
use Panlatent\SiteCli\Site\Manager;
use Panlatent\SiteCli\Site\NotFoundException;
use Panlatent\SiteCli\Support\Util;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
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

    /**
     * Command constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ($this instanceof Reloadable) {
            $this->addOption(
                'without-reload',
                null,
                InputOption::VALUE_NONE,
                'Without automatic reload service when change'
            );
        }
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $statusCode = parent::run($input, $output);

        if ($this->isReloadService($this, $input)) {
            if ($this->reloadService()) {
                $output->writeln('<info>Service has been reloaded!</info>');
            } else {
                $output->writeln('<error>Service reload failed!</error>');
            }
        }

        return $statusCode;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Panlatent\SiteCli\Site\NotFoundException
     */
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

    /**
     * @param object $object
     * @param InputInterface $input
     * @return bool
     */
    private function isReloadService($object, $input)
    {
        $can = $object instanceof Reloadable && ! $input->getOption('without-reload') &&
            $this->configure->get('service.reload', false);

        return $can && $object->canReloadService();
    }

    private function reloadService()
    {
        try {
            $command = $this->getApplication()->find('service');
            $arguments = [
                'signal' => 'reload',
            ];

            $buffer = new BufferedOutput();
            $completionInput = new ArrayInput($arguments);
            $statusCode = $command->run($completionInput, $buffer);
        } catch (Exception $e) {
            return false;
        }

        return $statusCode == 0;
    }
}