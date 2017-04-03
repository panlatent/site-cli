<?php

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\ConfManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupListCommand extends Command
{
    protected function configure()
    {
        $this->setName('group:list')
            ->setAliases(['groups']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $manager = $this->manager();
        foreach ($manager->getGroups() as $group) {
            $enable = "<info>{$group->getEnableSiteCount()}</info> enabled";
            $output->writeln(sprintf(" - %s : (%d site : %s)", $group->getName(), $group->count(), $enable));
        }
    }
}