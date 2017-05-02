<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    protected function configure()
    {
        $this->setName('config')
            ->setDescription('Setting your .site-cli.yml and edit site')
            ->addArgument('target', InputArgument::REQUIRED, 'Config argument');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        switch ($input->getArgument('target')) {
            case 'dump-complete':
                $this->createDumpCompleteFile();
                break;
        }
    }

    private function createDumpCompleteFile()
    {
        $program = $_SERVER['argv'][0];
        exec($program .  ' _completion --generate-hook', $output);
        $completion = implode("\n", $output);
        $content = file_get_contents(__DIR__ . '/../../.site-cli.sh');
        $content = str_replace('{% complete %}', $completion, $content);
        file_put_contents($this->config->getHome() . '.site-cli.sh', $content);
    }
}