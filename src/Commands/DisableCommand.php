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

class DisableCommand extends Command
{
    protected function configure()
    {
        $this->setName('disable')
            ->setDescription('Disable a site or a group sites')
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                'A group name or a site name, using group/site style'
            )->addOption(
                'clear-lost',
                null,
                InputOption::VALUE_NONE,
                'Clear lost symbolic links'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkLostSymbolicLink = false;
        parent::execute($input, $output);

        if ($input->getOption('clear-lost')) {
            foreach ($this->manager->getLostSymbolicLinkEnables() as $enable) {
                if (unlink($enable)) {
                    $output->writeln(sprintf("<comment>Success: Clean symbolic link lost file \"%s\"</comment>",
                        $enable));
                } else {
                    $output->writeln(sprintf("<error>Warning: remove symbolic link file \"%s\" fail!</error>",
                        $enable));
                }
            }
            if (empty($input->getArgument('target'))) {
                return;
            }
        }

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