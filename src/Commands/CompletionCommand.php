<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
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
         * Create command from argument
         */
        $handler->addHandler(new Completion\ShellPathCompletion(
            'create',
            'server-root',
            Completion::TYPE_OPTION
        ));
    }
}