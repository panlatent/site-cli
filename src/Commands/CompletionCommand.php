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
use Stecman\Component\Symfony\Console\BashCompletion\Completion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompletionCommand extends \Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand
{
    /**
     * @var CliConfig
     */
    protected $config;

    /**
     * @var ConfManager
     */
    protected $manager;

    protected function configure()
    {
        parent::configure();
        $this->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
         parent::execute($input, $output);
    }

    protected function configureCompletion(CompletionHandler $handler)
    {
        $handler->addHandler(new Completion(
            'config',
            'target',
            Completion::TYPE_ARGUMENT,
            [
                'init',
                'dump-complete'
            ]
        ));

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

        $handler->addHandler(new Completion(
            Completion::ALL_COMMANDS,
            'group',
            Completion::TYPE_ARGUMENT,
            function () {
                $this->getManager();
                $names = [];
                $groups = $this->manager->getGroups();
                foreach ($groups as $group) {
                    $names[] = $group->getName();
                }
                return $names;
            }
        ));

        $handler->addHandler(new Completion(
            Completion::ALL_COMMANDS,
            'site',
            Completion::TYPE_ARGUMENT,
            function () {
                $this->getManager();
                $context = $this->handler->getContext();
                $command = $context->getWordAtIndex(1);
                $names = [];
                $group = $this->manager->getGroup($context->getWordAtIndex(2));
                foreach ($group->getSites() as $site) {
                    if ($command == 'disable') {
                        if ($site->isEnable()) {
                            $names[] = $site->getName();
                        }
                    } elseif ($command == 'enable') {
                        if ( ! $site->isEnable()) {
                            $names[] = $site->getName();
                        }
                    } else {
                        $names[] = $site->getName();
                    }
                }
                return $names;
            }
        ));
    }

    protected function getManager()
    {
        $this->config = new CliConfig();
        $this->config->loadConfigure();
        $this->manager = new ConfManager($this->config['site']['available'], $this->config['site']['enabled']);
    }
}