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
use Panlatent\SiteCli\Service\ReloadTrait;
use Panlatent\SiteCli\Site\Group;
use Panlatent\SiteCli\Site\NotFoundException;
use Panlatent\SiteCli\Site\Site;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EnableCommand extends Command implements Reloadable
{
    use ReloadTrait;

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
        if (false === ($repo = $this->getManager()->filter()->id($input->getArgument('site')))) {
            throw new NotFoundException("Not found site");
        }
        if ($repo instanceof Group) {
            $this->enableGroup($repo, $isForce);
        } elseif ($repo instanceof Site) {
            $this->enableSite($repo, $isForce);
        }
    }

    protected function enableSite(Site $site, $isForce = false)
    {
        if ($site->isEnable() && ! $isForce) {
            $this->disableServiceReload();
            $this->io->writeln("<comment>{$site->getPrettyName()} is enabled, no need to repeat!</comment>");
            return;
        }

        $site->enable();
        $this->io->writeln("<info>{$site->getPrettyName()} enable success!</info>");
    }

    protected function enableGroup(Group $group, $isForce = false)
    {
        $this->io->writeln("<comment>Notice: {$group->count()} sites in {$group->getPrettyName()} group</comment>");

        $hasEnable = false;
        foreach ($group->filter()->sites() as $site) {
            if ($site->isEnable() && ! $isForce) {
                $this->io->writeln("<comment>x {$group->getPrettyName()}/{$site->getName()} is enabled, skip!</comment>");
                continue;
            }

            $site->enable();
            $hasEnable = true;
            $this->io->writeln("<info>√ {$group->getPrettyName()}/{$site->getName()} enable success!</info>");
        }

        if ( ! $hasEnable) {
            $this->disableServiceReload();
        }
    }
}