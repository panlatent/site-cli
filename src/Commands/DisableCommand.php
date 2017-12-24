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
use Panlatent\SiteCli\Site\NotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableCommand extends Command implements Reloadable
{
    use ReloadTrait;

    protected function configure()
    {
        $this->setName('disable')
            ->setDescription('Disable a site or a group sites')
            ->addArgument(
                'site',
                InputArgument::REQUIRED,
                'Site name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
        if (false === ($group = $this->getManager()->filter()->group($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        if (false === ($site = $group->filter()->site($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }
        if ( ! $site->isEnable()) {
            $this->disableServiceReload();
            $this->io->writeln("<comment>$groupName/$siteName is disabled, no need to repeat!</comment>");
            return;
        }

        $site->disable();
        $this->io->writeln("<info>$groupName/$siteName disable success!</info>");
    }

    protected function disableGroup($groupName)
    {
        if (false === ($group = $this->getManager()->filter()->group($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        $hasDisable = false;
        $this->io->writeln("<comment>Notice: {$group->count()} site in $groupName</comment>");
        foreach ($group->filter()->sites() as $site) {
            if ( ! $site->isEnable()) {
                $this->io->writeln("<comment>x $groupName/{$site->getName()} is disabled, skip!</comment>");
                continue;
            }

            $site->disable();
            $hasDisable = true;
            $this->io->writeln("<info>√ $groupName/{$site->getName()} enable success!</info>");
        }

        if ( ! $hasDisable) {
            $this->disableServiceReload();
        }
    }
}