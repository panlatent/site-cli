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

class ClearCommand extends Command
{
    protected function configure()
    {
        $this->setName('clear')
            ->setDescription('Clear unless symbolic links');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getManager()->getLostSymbolicLinkEnables() as $enable) {
            if (unlink($enable)) {
                $output->writeln(sprintf("<comment>Success: Clean symbolic link lost file \"%s\"</comment>",
                    $enable));
            } else {
                $output->writeln(sprintf("<error>Warning: remove symbolic link file \"%s\" fail!</error>",
                    $enable));
            }
        }
    }
}