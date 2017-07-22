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
use Panlatent\SiteCli\Site\NotFoundException;
use Panlatent\SiteCli\Site\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EnableCommand extends Command implements Reloadable
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
        $this->setName('enable')
            ->setDescription('Enable a site or a group sites')
            ->addArgument(
                'site',
                InputArgument::REQUIRED,
                'Site name'
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
        $isForce = $input->getOption('force');
        if (false === ($pos = strpos($input->getArgument('site'), '/'))) {
            $this->enableGroup($input->getArgument('site'), $isForce);
        } else {
            $groupName = substr($input->getArgument('site'), 0, $pos);
            $siteName = substr($input->getArgument('site'), $pos + 1);
            $this->enableSite($groupName, $siteName, $isForce);
        }
    }

    protected function enableSite($groupName, $siteName, $isForce = false)
    {
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        if (false === ($site = $group->getSite($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }
        if ($site->isEnable() && ! $isForce) {
            $this->io->writeln("<comment>$groupName/$siteName is enabled, no need to repeat!</comment>");
            return;
        }

        $site->enable();
        $this->io->writeln("<info>$groupName/$siteName enable success!</info>");
    }

    protected function enableGroup($groupName, $isForce = false)
    {
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        $this->io->writeln("<comment>Notice: {$group->count()} site in $groupName</comment>");
        foreach ($group->getSites() as $site) {
            if ($site->isEnable() && ! $isForce) {
                $this->io->writeln("<comment>x $groupName/{$site->getName()} is enabled, skip!</comment>");
                continue;
            }

            $site->enable();
            $this->io->writeln("<info>√ $groupName/{$site->getName()} enable success!</info>");
        }
    }
}