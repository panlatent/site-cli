<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Exception;
use Panlatent\SiteCli\Configure;
use Panlatent\SiteCli\Support\Util;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
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
            ->setDescription('Get and set site-cli options')
            ->addArgument('name', InputArgument::OPTIONAL, 'config name')
            ->addArgument('value', InputArgument::OPTIONAL, 'Set config value');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ( ! $input->getArgument('value')) {
            $this->show($input->getArgument('name'));
        } else {
            try {
                $this->save($input->getArgument('name'), $input->getArgument('value'));
            } catch (Exception $exception) {
                $this->io->writeln("<error>Configure item set failed! {$exception->getMessage()}</error>");
            }
        }
    }

    protected function show($name)
    {
        if (empty($name)) {
            $items = $this->configure->all();
        } else {
            $items = $this->configure->get($name);
        }

        $this->writelnRecursion($items);
    }

    protected function save($name, $value)
    {
        $old = $this->configure->get($name);
        if ($old == $value) {
            return;
        }
        $configure = new Configure(['?~/.site-cli.yml']);
        $configure->set($name, $value);
        $this->configure->save($configure);
    }

    protected function writelnRecursion($items, $prefix = '')
    {
        if (is_array($items)) {
            foreach ($items as $key => $item) {
                if (is_array($item)) {
                    $this->writelnRecursion($item, $prefix . $key . '.');
                } else {
                    $this->io->writeln($prefix . $key . ' = ' . $item);
                }
            }
        } else {
            $this->io->writeln($items);
        }
    }

    protected function getArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName == 'name') {
            return Util::arrayDotKeys($this->configure->all());
        }

        return parent::completeArgumentValues($argumentName, $context);
    }
}