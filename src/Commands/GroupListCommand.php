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

class GroupListCommand extends Command
{
    protected function configure()
    {
        $this->setName('group:list')
            ->setAliases(['groups'])
            ->setDescription('Lists all site groups');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $groups = $this->manager->getGroups();
        sort($groups);
        foreach ($groups as $group) {
            $enable = "<info>{$group->getEnableSiteCount()}</info> enabled";
            $output->writeln(sprintf(" - <info>%s</info>: %d site, %s", $group->getName(), $group->count(), $enable));
        }
    }
}