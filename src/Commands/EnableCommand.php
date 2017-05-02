<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\NotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EnableCommand extends Command
{
    protected function configure()
    {
        $this->setName('enable')
            ->setDescription('Enable a site or a group sites')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'A group name'
            )
            ->addArgument(
                'site',
                InputArgument::OPTIONAL,
                'a site name in the group'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force create a symbolic link, whether it exists or not'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        if (empty($input->getArgument('site'))) {
            $this->enableGroup($input, $output);
        } else {
            $this->enableSite($input, $output);
        }
    }

    protected function enableSite(InputInterface $input, OutputInterface $output)
    {
        $groupName = $input->getArgument('group');
        $siteName = $input->getArgument('site');
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        if (false === ($site = $group->getSite($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }
        if ($site->isEnable() && ! $input->getOption('force')) {
            $output->writeln("<comment>$groupName / $siteName is enabled, no need to repeat!</comment>");
            return;
        }

        $site->enable();
        $output->writeln("<info>$groupName / $siteName enable success!</info>");
    }

    protected function enableGroup(InputInterface $input, OutputInterface $output)
    {
        $groupName = $groupName = $input->getArgument('group');
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        $output->writeln("<comment>Notice: {$group->count()} site in $groupName</comment>");
        foreach ($group->getSites() as $site) {
            if ($site->isEnable() && ! $input->getOption('force')) {
                $output->writeln("<comment>x $groupName / {$site->getName()} is enabled, skip!</comment>");
                continue;
            }

            $site->enable();
            $output->writeln("<info>√ $groupName / {$site->getName()} enable success!</info>");
        }
    }
}