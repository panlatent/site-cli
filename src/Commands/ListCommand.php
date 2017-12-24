<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Site\Site;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected $isAll;

    protected $isLong;

    protected $group;

    protected $site;

    protected function configure()
    {
        $this->setName('ls')
            ->setDescription('List groups and sites contents')
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

    /**
     * Print groups list.
     *
     * @throws \Exception
     */
    protected function listGroups()
    {
        $groups = $this->getManager()->filter()->groups();
        if ($groups->isEmpty()) {
            $this->io->writeln('(empty)');
            return;
        }

        $groups->ksort();
        if ($this->isLong) {
            $list = [];
            foreach ($groups as $group) {
                $list[] = [$group->count(), $group->filter()->enableCount(), $group->getName()];
            }
            $this->io->table(['enabled', 'size', 'group'], $list);
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
        $sites = $this->getManager()->filter()->sites();
        if ($this->group) {
            $sites = array_filter($sites, function ($site) {
                /** @var \Panlatent\SiteCli\Site\Site $site */
                return $site->getGroup()->getName() == $this->group;
            });
        }
        if ( ! $this->isAll) {
            $sites = $sites->filter(function ($site) {
                /** @var \Panlatent\SiteCli\Site\Site $site */
                return $site instanceof Site && $site->isEnable();
            });
        }
        if (empty($sites)) {
            $this->io->writeln('(empty)');
            return;
        }
        $sites->ksort();
        if ($this->isLong) {
            $list = [];
            foreach ($sites as $site) {
                $status = $site->isEnable() ? 'o' : '-';
                $name = $site->getPrettyName();
                $name = $site->isEnable() ? '<enable>' . $name .'</enable>' :  $name;
                $count = $site->count();
                $list[] = [
                    $status,
                    $count,
                    $name,
                ];

            }
            $this->io->table([ 'status', 'server', 'site'], $list);
        } else {
            $list = [];
            foreach ($sites as $site) {
                $list[] = $site->isEnable() ? '<enable>' . $site->getName() . '</enable>' : $site->getName();
            }
            $this->io->writeln($list);
        }
    }

    protected function listServers()
    {
        $servers = $this->getManager()->filter()->servers();
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
        if (empty($servers)) {
            $this->io->writeln('(empty)');
            return;
        }

        ksort($servers);
        if ($this->isLong) {
            $list = [];
            foreach ($servers as $server) {
                $name = $server->getSite()->isEnable() ? '<enable>' . $server->getName() . '</enable>' :
                    $server->getName();
                $status = $server->getSite()->isEnable() ? 'o' : '-';
                $list[] = [
                    $status,
                    $server->getSite()->getGroup()->getName(),
                    $server->getSite()->getName(),
                    implode(',', $server->getListens()),
                    $name,
                ];
            }
            $this->io->table(['status', 'group', 'site', 'listen','server'], $list);
        } else {
            $list = [];
            foreach ($servers as $server) {
                $list[] = $server->getName();
            }
            $this->io->writeln($list);
        }
    }
}