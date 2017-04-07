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

class GroupEnableCommand extends Command
{
    protected function configure()
    {
        $this->setName('group:enable')
            ->setDescription('Enable a group sites')
            ->addArgument('group', InputArgument::REQUIRED, 'Enable a group sites');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $groupName = $input->getArgument('group');
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        $output->writeln("<comment>Notice: {$group->count()} site in $groupName</comment>");
        foreach ($group->getSites() as $site) {
            if ($site->isEnable()) {
                $output->writeln("<comment>x $groupName / {$site->getName()} is enabled, skip!</comment>");
                continue;
            }

            $site->enable();
            $output->writeln("<info>√ $groupName / {$site->getName()} enable success!</info>");
        }
    }
}