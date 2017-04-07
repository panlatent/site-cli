<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Exception;
use Panlatent\SiteCli\NotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteEnableCommand extends Command
{
    protected function configure()
    {
        $this->setName('site:enable')
            ->setAliases(['enable'])
            ->setDescription('Enable a site in a group')
            ->addArgument('site', InputArgument::REQUIRED, 'Enable a site: group/site');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        if (false === strpos($input->getArgument('site'), '/')) { // Default group
            if (false === ($group = $this->manager->getGroup(':default'))) {
                throw new NotFoundException("Not found default group");
            }
            $groupName = 'default';
            $siteName = $input->getArgument('site');
        } else {
            if ( ! preg_match('#^.+/.+$#', $input->getArgument('site'))) {
                throw new Exception("Unknown site argument syntax, should give \"group/site\"");
            }
            list($groupName, $siteName) = explode('/', $input->getArgument('site'), 2);
            if (false === ($group = $this->manager->getGroup($groupName))) {
                throw new NotFoundException("Not found site group \"$groupName\"");
            }
        }

        if (false === ($site = $group->getSite($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }
        if ($site->isEnable()) {
            $output->writeln("<comment>$groupName / $siteName is enabled, no need to repeat!</comment>");
            return;
        }

        $site->enable();
        $output->writeln("<info>$groupName / $siteName enable success!</info>");
    }
}