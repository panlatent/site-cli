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

class DisableCommand extends Command
{
    protected function configure()
    {
        $this->setName('disable')
            ->setDescription('Disable a site or a group sites')
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'A group name or a site name, using group/site style'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        if (preg_match('#^.+/.+$#', $input->getArgument('target'))) {
            $this->disableSite($input, $output);
        } else {
            $this->disableGroup($input, $output);
        }
    }

    protected function disableSite(InputInterface $input, OutputInterface $output)
    {
        list($groupName, $siteName) = explode('/', $input->getArgument('target'), 2);
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        if (false === ($site = $group->getSite($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }
        if ( ! $site->isEnable()) {
            $output->writeln("<comment>$groupName / $siteName is disabled, no need to repeat!</comment>");
            return;
        }

        $site->disable();
        $output->writeln("<info>$groupName / $siteName disable success!</info>");
    }

    protected function disableGroup(InputInterface $input, OutputInterface $output)
    {
        $groupName = $input->getArgument('target');
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        $output->writeln("<comment>Notice: {$group->count()} site in $groupName</comment>");
        foreach ($group->getSites() as $site) {
            if ( ! $site->isEnable()) {
                $output->writeln("<comment>x $groupName / {$site->getName()} is disabled, skip!</comment>");
                continue;
            }

            $site->disable();
            $output->writeln("<info>√ $groupName / {$site->getName()} enable success!</info>");
        }
    }
}