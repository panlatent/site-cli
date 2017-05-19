<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Configure;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    /**
     * @var \Panlatent\SiteCli\Configure
     */
    protected $configure;

    protected function configure()
    {
        $this->setName('config')
            ->setDescription('Setting your .site-cli.yml and edit site')
            ->addArgument('name', InputArgument::REQUIRED, 'config name')
            ->addArgument('value', InputArgument::OPTIONAL, 'Set config value');
    }

    public function register(Configure $configure)
    {
        $this->configure = $configure;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ( ! $input->getArgument('value')) {
            $this->showItem($input->getArgument('name'));
        }
    }

    protected function showItem($name)
    {
        $this->io->writeln($this->configure->get($name));
    }
}