<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListServerCommand extends Command
{
    protected function configure()
    {
        $this->setName('list:server')
            ->setAliases(['servers'])
            ->setDescription('Lists all server')
            ->addOption(
                'enable',
                'e',
                InputOption::VALUE_NONE,
                'Show only enabled sites'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $servers = $this->manager->getServers();

        if ($input->getOption('enable')) {
            $servers = array_filter($servers, function($server) {
                /** @var \Panlatent\SiteCli\SiteServer $server */
                return $server->getSite()->isEnable();
            });
        }

        sort($servers);
        foreach ($servers as $server) {
            $status = $server->getSite()->isEnable() ? '<info>√</info>' : '<comment>x</comment>';
            $output->writeln(sprintf(" - %s server <info>%s</info> listen <info>%s</info> on <comment>%s/%s</comment>",
                $status,
                $server->getName(),
                $server->getListen(),
                $server->getSite()->getGroup()->getName(),
                $server->getSite()->getName()));
        }
    }
}