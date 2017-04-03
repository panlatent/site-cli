<?php

namespace Panlatent\SiteCli\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteListCommand extends Command
{
    protected function configure()
    {
        $this->setName('site:list')
            ->setAliases(['sites']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $manager = $this->manager();
        foreach ($manager->getSites() as $site) {
            $status = $site->isEnable() ? '<info>√</info>' : '<comment>x</comment>';
            $output->writeln(sprintf(" - %s / %s  %s", $site->getGroup()->getName(), $site->getName(), $status));
        }
    }
}