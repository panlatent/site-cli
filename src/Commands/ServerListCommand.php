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
use Symfony\Component\Console\Output\OutputInterface;

class ServerListCommand extends Command
{
    protected function configure()
    {
        $this->setName('server:list')
            ->setAliases(['servers'])
            ->setDescription('Lists all server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $manager = $this->manager();
        $servers = $manager->getServers();
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