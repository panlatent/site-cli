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

class SiteListCommand extends Command
{
    protected function configure()
    {
        $this->setName('site:list')
            ->setAliases(['sites'])
            ->setDescription('Lists all sites in a group');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $manager = $this->manager();
        $sites = $manager->getSites();
        sort($sites);
        foreach ($sites as $site) {
            $status = $site->isEnable() ? '<info>√</info>' : '<comment>x</comment>';
            $output->writeln(sprintf(" - %s / %s  %s", $site->getGroup()->getName(), $site->getName(), $status));
        }
    }
}