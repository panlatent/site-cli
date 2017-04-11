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

class ListSiteCommand extends Command
{
    protected function configure()
    {
        $this->setName('list:site')
            ->setAliases(['sites'])
            ->setDescription('Lists all sites in a group');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $sites = $this->manager->getSites();
        sort($sites);
        foreach ($sites as $site) {
            $status = $site->isEnable() ? '<info>√</info>' : '<comment>x</comment>';
            $name = $site->isEnable() ? '<info>%s/%s</info>' : '<comment>%s/%s</comment>';
            $count = $site->count();
            $output->writeln(sprintf(" - %s $name [%d]",
                $status,
                $site->getGroup()->getName(),
                $site->getName(),
                $count
                ));
        }
    }
}