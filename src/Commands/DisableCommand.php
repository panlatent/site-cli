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
        if (false === ($repo = $this->getManager()->filter()->id($input->getArgument('site')))) {
            throw new NotFoundException("Not found " . $input->getArgument('site'));
        }
        if ($repo instanceof Group) {
            $this->disableGroup($repo);
        } elseif ($repo instanceof Site) {
            $this->disableSite($repo);
        }
    }

    protected function disableSite(Site $site)
    {
        if ( ! $site->isEnable()) {
            $this->disableServiceReload();
            $this->io->writeln("<comment>{$site->getPrettyName()} is disabled, no need to repeat!</comment>");
            return;
        }

        $site->disable();
        $this->io->writeln("<info>{$site->getPrettyName()} disable success!</info>");
    }

    protected function disableGroup(Group $group)
    {
        $hasDisable = false;
        $this->io->writeln("<comment>Notice: {$group->count()} site in {$group->getPrettyName()}</comment>");
        foreach ($group->filter()->sites() as $site) {
            if ( ! $site->isEnable()) {
                $this->io->writeln("<comment>x {$group->getPrettyName()}/{$site->getName()} is disabled, skip!</comment>");
                continue;
            }

            $site->disable();
            $hasDisable = true;
            $this->io->writeln("<info>√ {$group->getPrettyName()}/{$site->getName()} enable success!</info>");
        }

        if ( ! $hasDisable) {
            $this->disableServiceReload();
        }
    }
}