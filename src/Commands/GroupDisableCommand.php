<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\NotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupDisableCommand extends Command
{
    protected function configure()
    {
        $this->setName('group:disable')
            ->setDescription('Enable a group sites')
            ->addArgument('group', InputArgument::REQUIRED, 'Disable a group sites');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $manager = $this->manager();
        $groupName = $input->getArgument('group');
        if (false === ($group = $manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        $output->writeln("<comment>Notice: {$group->count()} site in $groupName</comment>");
        foreach ($group->getSites() as $site) {
            if ( ! $site->isEnable()) {
                $output->writeln("<comment>x $groupName / {$site->getName()} is disabled, skip!</comment>");
                continue;
            }

            $site->disable();
            $output->writeln("<info>√ $groupName / {$site->getName()} enable success!</info>");
        }
    }
}