<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Site\Group;
use Panlatent\SiteCli\Site\Site;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AlfredCommand extends Command
{
    protected function configure()
    {
        $this->setName('alfred')
            ->setDescription('Alfred 3 workflow support')
            ->setHidden(true)
            ->addOption(
                'keyword',
                'k',
                InputOption::VALUE_OPTIONAL,
                'Query keyword'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $items = [];

        $keyword = $input->getOption('keyword');
        if (substr(rtrim($keyword), -1) == '>') {
            $name = rtrim(substr(rtrim($keyword), 0, -1));
            $id = preg_replace('#\s*>\s*#', '/', $name);
            $repo = $this->getManager()->filter()->id($id);
            if ($repo instanceof Site) {
                foreach ($repo->filter()->servers() as $server) {
                    $item = [];
                    $arg = $server->getFirstUrl();
                    $item['title'] = $server->getName();
                    $item['subtitle'] = $arg;
                    $item['arg'] = $arg;
                    $item['icon'] = ['path' => 'icon.png'];
                    $items[] = $item;
                }
            } elseif ($repo instanceof Group) {
                $items = $this->getSiteItems();
            }
        } else {
            $items = $this->getSiteItems();
        }

        if (false !== ($keyword)) {
            $items = $this->search($keyword, $items);
        }

        $this->render([
            'items' => $items,
        ]);
    }

    protected function render($data)
    {
        $this->io->writeln(json_encode($data));
    }

    protected function getSiteItems()
    {
        $items = [];
        foreach ($this->getManager()->filter()->sites() as $site) {
            $servers = $site->getServers();
            $items[] = [
                'title'        => $site->getName(),
                'subtitle'     => str_replace('/', ' > ', $site->getPrettyName()),
                'arg'          => ($site->isEnable() ? 'disable ' : 'enable ') . $site->getPrettyName(),
                'icon'         => ['path' => $site->isEnable() ? 'on.png' : 'off.png'],
                'autocomplete' => str_replace('/', '>', $site->getPrettyName()) . '>',
                'mods'         => [
                    'cmd' => [
                        "valid"    => true,
                        "arg"      => isset($servers[0]) ? $servers[0]->getFirstUrl() : '',
                        "subtitle" => isset($servers[0]) ? $servers[0]->getFirstUrl() : 'No server',
                    ],
                    'alt' => [
                        "valid"    => true,
                        "arg"      => $site->getPath(),
                        "subtitle" => $site->getPath(),
                    ],
                ],
            ];
        };

        return $items;
    }

    protected function search($keyword, $items)
    {
        foreach ($items as $key => $item) {
            similar_text($keyword, $item['title'], $level);
            if (false !== ($pos = strpos($item['title'], $keyword))) {
                if ($pos == 0) {
                    $level *= 2;
                }
                $level += 100 - $pos;
            }
            $items[$key]['level'] = $level;
        }
        usort($items, function ($a, $b) {
            return -($a['level'] - $b['level']);
        });

        return $items;
    }
}