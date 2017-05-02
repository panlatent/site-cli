<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;

class CompletionCommand extends \Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setHidden(true);
    }

    protected function configureCompletion(CompletionHandler $handler)
    {
        parent::configureCompletion($handler);
    }
}