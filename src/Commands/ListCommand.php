<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Site\NotFoundException;
use Panlatent\SiteCli\Site\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
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
        $this->setName('list')
            ->setDescription('Lists sites or groups or servers')
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'sites/groups/servers',
                'sites'
            )
            ->addOption(
                'enable',
                'e',
                InputOption::VALUE_NONE,
                'Show only enabled sites'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');

        switch ($type) {
            case 'sites':
                $this->listSites($input->getOption('enable'));
                break;
            case 'groups':
                $this->listGroups();
                break;
            case 'servers':
                $this->listServers($input->getOption('enable'));
                break;
            default:
                throw new NotFoundException("Not found list type: $type");
        }
    }

    protected function listSites($enable = false)
    {
        $sites = $this->manager->getSites();
        if ($enable) {
            $sites = array_filter($sites, function($site) {
                /** @var \Panlatent\SiteCli\Site\Site $site */
                return $site->isEnable();
            });
        }

        sort($sites);
        foreach ($sites as $site) {
            $status = $site->isEnable() ? '<info>√</info>' : '<comment>x</comment>';
            $name = $site->isEnable() ? '<info>%s/%s</info>' : '<comment>%s/%s</comment>';
            $count = $site->count();
            $this->io->writeln(sprintf(" - %s $name [%d]",
                $status,
                $site->getGroup()->getName(),
                $site->getName(),
                $count
            ));
        }
    }

    protected function listGroups()
    {
        $groups = $this->manager->getGroups();
        sort($groups);
        foreach ($groups as $group) {
            $enable = "<info>{$group->getEnableSiteCount()}</info> enabled";
            $this->io->writeln(sprintf(" - <info>%s</info>: %d site, %s",
                $group->getName(), $group->count(), $enable));
        }
    }

    protected function listServers($enable = false)
    {
        $servers = $this->manager->getServers();

        if ($enable) {
            $servers = array_filter($servers, function($server) {
                /** @var \Panlatent\SiteCli\Site\Server $server */
                return $server->getSite()->isEnable();
            });
        }

        sort($servers);
        foreach ($servers as $server) {
            $status = $server->getSite()->isEnable() ? '<info>√</info>' : '<comment>x</comment>';
            $this->io->writeln(sprintf(" - %s server <info>%s</info> listen <info>%s</info> on <comment>%s/%s</comment>",
                $status,
                $server->getName(),
                $server->getListen(),
                $server->getSite()->getGroup()->getName(),
                $server->getSite()->getName()));
        }
    }
}