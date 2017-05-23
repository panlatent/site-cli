<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

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

    protected $isAll;

    protected $isLong;

    protected $group;

    protected $site;

    public function register(Manager $manager)
    {
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this->setName('list')
            ->setDescription('Lists sites or groups or servers')
            ->addArgument(
                'site',
                InputArgument::OPTIONAL,
                'List from path'
            )
            ->addOption(
                'filter',
                'f',
                InputOption::VALUE_REQUIRED,
                'Filter list type'
            )
            ->addOption(
                'server',
                's',
                InputOption::VALUE_NONE,
                'Filter server list type [--filter=server]'
            )
            ->addOption(
                'group',
                'g',
                InputOption::VALUE_NONE,
                'Filter group list type [--filter=group]'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Include disable sites'
            )
            ->addOption(
                'long',
                'l',
                InputOption::VALUE_NONE,
                'Show long list'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $site = $input->getArgument('site');
        $this->isAll = $input->getOption('all');
        $this->isLong = $input->getOption('long');

        $type = '';
        if ($input->getOption('filter')) {
            switch ($input->getOption('filter')) {
                case 's':
                case 'server':
                    $type = 'server';
                    break;
                case 'g':
                case 'group':
                    $type = 'group';
                    break;
                case 'site':
                    $type = 'site';
                    break;
                default:
                    return;
            }
        }

        if (empty($type)) {
            if ($input->getOption('group')) {
                $type = 'group';
            } elseif ($input->getOption('server')) {
                $type = 'server';
            }
        }

        if ($type == 'group') {
            $this->listGroups();
        } elseif ($type == 'server') {
            $this->listServers();
        } elseif ($site) {
            if (false === ($pos = strpos($site, '/'))) {
                $this->group = $site;
                $this->listSites();
            } else {
                $this->group = substr($site, 0, $pos);
                $this->site = substr($site, $pos + 1);
                $this->listServers();
            }
        } else {
            $this->listSites();
        }
    }

    protected function listGroups()
    {
        $groups = $this->manager->getGroups();
        sort($groups);
        if ($this->isLong) {
            foreach ($groups as $group) {
                $enable = "<info>{$group->getEnableSiteCount()}</info> enabled";
                $this->io->writeln(sprintf(" - <info>%s</info>: %d site, %s",
                    $group->getName(), $group->count(), $enable));
            }
        } else {
            $list = [];
            foreach ($groups as $group) {
                $list[] = $group->getName();
            }
            $this->io->writeln($list);
        }
    }

    protected function listSites()
    {
        $sites = $this->manager->getSites();
        if ($this->group) {
            $sites = array_filter($sites, function ($site) {
                /** @var \Panlatent\SiteCli\Site\Site $site */
                return $site->getGroup()->getName() == $this->group;
            });
        }
        if ( ! $this->isAll) {
            $sites = array_filter($sites, function ($site) {
                /** @var \Panlatent\SiteCli\Site\Site $site */
                return $site->isEnable();
            });
        }
        sort($sites);
        if ($this->isLong) {
            $list = [];
            foreach ($sites as $site) {
                $status = $site->isEnable() ? 'T' : 'F';
                $name = $site->getGroup()->getName() . '/' .$site->getName();
                $name = $site->isEnable() ? '<info>' . $name .'</info>' : '<comment>' . $name .'</comment>';
                $count = $site->count();
                $list[] = [
                    $status,
                    $count,
                    $name
                ];

            }
            $this->io->table([], $list);
        } else {
            $list = [];
            foreach ($sites as $site) {
                $list[] = $site->getName();
            }
            $this->io->writeln($list);
        }
    }

    protected function listServers()
    {
        $servers = $this->manager->getServers();
        if ($this->group && $this->site) {
            $servers = array_filter($servers, function ($server) {
                /** @var \Panlatent\SiteCli\Site\Server $server */
                return $server->getSite()->getGroup()->getName() == $this->group
                    && $server->getSite()->getName() == $this->site;
            });
        }
        if ( ! $this->isAll) {
            $servers = array_filter($servers, function ($server) {
                /** @var \Panlatent\SiteCli\Site\Server $server */
                return $server->getSite()->isEnable();
            });
        }

        sort($servers);
        if ($this->isLong) {
            $list = [];
            foreach ($servers as $server) {
                $status = $server->getSite()->isEnable() ? '<info>√</info>' : '<comment>x</comment>';
                $list[] = [
                    $status,
                    $server->getName(),
                    $server->getListen(),
                    $server->getSite()->getGroup()->getName(),
                    $server->getSite()->getName()
                ];
            }
            $this->io->table([], $list);
        } else {
            $list = [];
            foreach ($servers as $server) {
                $list[] = $server->getName();
            }
            $this->io->writeln($list);
        }

    }
}