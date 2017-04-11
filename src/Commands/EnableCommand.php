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
                'target',
                InputArgument::REQUIRED,
                'A group name or a site name, using group/site style'
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
        if (preg_match('#^.+/.+$#', $input->getArgument('target'))) {
            $this->enableSite($input, $output);
        } else {
            $this->enableGroup($input, $output);
        }
    }

    protected function enableSite(InputInterface $input, OutputInterface $output)
    {
        list($groupName, $siteName) = explode('/', $input->getArgument('target'), 2);
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
        $groupName = $input->getArgument('target');
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