<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Symfony\Component\Console\Command\ListCommand;

final class DefaultCommand extends ListCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('list')
            ->setHidden(true);
    }
}