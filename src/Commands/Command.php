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
use Panlatent\SiteCli\Support\Util;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
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
abstract class Command extends \Symfony\Component\Console\Command\Command implements CompletionAwareInterface
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
     * @var \Panlatent\SiteCli\Site\Manager
     */
    private $manager;

    /**
     * Command constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    final protected function preInit()
    {
        $this->application = $this->getApplication();
        $this->container = $this->application->getContainer();
        $this->configure = $this->container[Configure::class];

        if ($this instanceof Reloadable) {
            $this->addOption(
                'without-reload',
                null,
                InputOption::VALUE_NONE,
                'Without automatic reload service when change'
            );
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    final protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->preInit();

        $this->io = new SymfonyStyle($input, $output);
        $this->io->getFormatter()->setStyle('enable', new OutputFormatterStyle('white', null, ['bold']));
        $this->container->setService(SymfonyStyle::class, $this->io);

        if ( ! method_exists($this, 'register') ||
            ! is_callable([$this, 'register'])) {
            return;
        }
        $this->container->injectMethod($this, 'register');
        if ($this->configure['validate']['lost-symbolic-link']) {
            $this->checkLostSymbolicLink($output);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        if ( ! file_exists(Util::home() . '/.site-cli.yml')) {
            if ($this->io->confirm('Create a .site-cli.yml file to your home?', true)) {
                $this->createUserConfigure($output);
            }
        }

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

    final public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        $this->preInit();

        return $this->getArgumentValues($argumentName, $context);
    }

    final public function completeOptionValues($optionName, CompletionContext $context)
    {
        $this->preInit();

        return $this->getOptionValues($optionName, $context);
    }

    protected function getArgumentValues($argumentName, CompletionContext $context)
    {
        $argumentNameMethod = 'getArgument' . Util::strConvertCamel($argumentName);
        if (method_exists($this, $argumentNameMethod)) {
            return call_user_func([$this, $argumentNameMethod], $context);
        }

        return [];
    }

    protected function getOptionValues($optionName, CompletionContext $context)
    {
        $optionNameMethod = 'getOption' . Util::strConvertCamel($optionName);
        if (method_exists($this, $optionNameMethod)) {
            return call_user_func([$this, $optionNameMethod], $context);
        }

        return [];
    }

    /**
     * @param bool $throwException
     * @return bool|Manager
     * @throws Exception
     */
    protected function getManager($throwException = true)
    {
        if ($this->manager === null) {
            try {
                $this->manager = $this->container[Manager::class];
            } catch (Exception $exception) {
                if ($throwException) {
                    throw $exception;
                }

                return false;
            }
        }

        return $this->manager;
    }

    protected function getArgumentGroup()
    {
        $names = [];
        if ($manager = $this->getManager(false)) {
            foreach ($manager as $group) {
                $names[] = $group->getName();
            }
        }

        return $names;
    }

    protected function getArgumentSite(CompletionContext $context)
    {
        $command = $context->getWordAtIndex(1);
        $sites = [];
        if ($manager = $this->getManager(false)) {
            foreach ($manager->filter()->sites() as $site) {
                if ($command != 'disable' || $site->isEnable()) {
                    $sites[] = $site->getPrettyName();
                }
            }
        }

        return $sites;
    }

    private function createUserConfigure($output)
    {
        $command = $this->getApplication()->find('config');
        $arguments = [
            'command' => 'init',
        ];
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
     * @param object         $object
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