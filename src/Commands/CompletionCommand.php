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
use Stecman\Component\Symfony\Console\BashCompletion\Completion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompletionCommand extends \Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand
{
    /**
     * @var \Panlatent\SiteCli\Application
     */
    protected $application;

    /**
     * @var \Panlatent\Container\Container
     */
    protected $container;

    protected function configure()
    {
        parent::configure();
        $this->setHidden(true);
    }

    protected function initialize(
        InputInterface $input,
        OutputInterface $output
    ) {
        parent::initialize($input,
            $output);
        $this->application = $this->getApplication();
        $this->container = $this->application->getContainer();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
         parent::execute($input, $output);
    }

    protected function configureCompletion(CompletionHandler $handler)
    {
        /*
         * Config Command
         */
        $handler->addHandler(new Completion(
            'config',
            'name',
            Completion::TYPE_ARGUMENT,
            function () {
                /** @var \Panlatent\SiteCli\Configure $config */
                $config = $this->container->get(Configure::class);
                return  Util::arrayDotKeys($config->all());
            }
        ));

        /*
         * List Command
         */
        $handler->addHandler(new Completion(
            'list',
            'type',
            Completion::TYPE_ARGUMENT,
            [
                'groups',
                'sites',
                'servers',
            ]
        ));

        /*
         * All commands group argument
         */
        $handler->addHandler(new Completion(
            Completion::ALL_COMMANDS,
            'group',
            Completion::TYPE_ARGUMENT,
            function () {
                $names = [];
                if ($manager = $this->getManager()) {
                    $groups = $manager->getGroups();
                    foreach ($groups as $group) {
                        $names[] = $group->getName();
                    }
                }
                return $names;
            }
        ));

        /*
         * All commands site argument
         */
        $handler->addHandler(new Completion(
            Completion::ALL_COMMANDS,
            'site',
            Completion::TYPE_ARGUMENT,
            function () {
                $context = $this->handler->getContext();
                $command = $context->getWordAtIndex(1);

                $sites = [];
                if ($manager = $this->getManager()) {
                    $groups = $manager->getGroups();
                    foreach ($groups as $group) {
                        $sites[] = $group->getName();
                        foreach ($group->getSites() as $site) {
                            if ($command != 'disable' || $site->isEnable()) {
                                $sites[] = $group->getName() . '/' . $site->getName();
                            }
                        }
                    }
                }
                return $sites;
            }
        ));

        /*
         * Edit command
         */
        $handler->addHandler(new Completion(
            'edit',
            'editor',
            Completion::TYPE_OPTION,
            [
                'vim',
                'sublime'
            ]
        ));
    }

    /**
     * @return \Panlatent\SiteCli\Site\Manager|bool
     */
    private function getManager()
    {
        try {
            return $this->container[Manager::class];
        } catch (NotFoundException $e) {
            return false;
        }
    }
}