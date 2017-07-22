<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Service\Reloadable;
use Panlatent\SiteCli\Site\Manager;
use Panlatent\SiteCli\Site\NotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DisableCommand extends Command implements Reloadable
{
    /**
     * @var \Panlatent\SiteCli\Site\Manager
     */
    protected $manager;

    public function register(Manager $manager)
    {
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this->setName('disable')
            ->setDescription('Disable a site or a group sites')
            ->addArgument(
                'site',
                InputArgument::REQUIRED,
                'Site name'
            )
            ->addOption(
                'clear-lost',
                null,
                InputOption::VALUE_NONE,
                'Clear lost symbolic links'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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

        if (false === ($pos = strpos($input->getArgument('site'), '/'))) {
            $this->disableGroup($input->getArgument('site'));
        } else {
            $groupName = substr($input->getArgument('site'), 0, $pos);
            $siteName = substr($input->getArgument('site'), $pos + 1);
            $this->disableSite($groupName, $siteName);
        }
    }

    protected function disableSite($groupName, $siteName)
    {
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        if (false === ($site = $group->getSite($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }
        if ( ! $site->isEnable()) {
            $this->io->writeln("<comment>$groupName/$siteName is disabled, no need to repeat!</comment>");
            return;
        }

        $site->disable();
        $this->io->writeln("<info>$groupName/$siteName disable success!</info>");
    }

    protected function disableGroup($groupName)
    {
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        $this->io->writeln("<comment>Notice: {$group->count()} site in $groupName</comment>");
        foreach ($group->getSites() as $site) {
            if ( ! $site->isEnable()) {
                $this->io->writeln("<comment>x $groupName/{$site->getName()} is disabled, skip!</comment>");
                continue;
            }

            $site->disable();
            $this->io->writeln("<info>√ $groupName/{$site->getName()} enable success!</info>");
        }
    }
}