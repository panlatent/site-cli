<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Control\Service;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceCommand extends Command
{
    protected function configure()
    {
        $this->setName('service')
            ->setDescription('Control site service process')
            ->addArgument(
                'signal',
                InputArgument::REQUIRED,
                'service process signal'
            )->addOption(
                'echo',
                'i',
                InputOption::VALUE_NONE,
                'Echo command string send signal'
            )->addOption(
                'template',
                't',
                InputOption::VALUE_OPTIONAL,
                'Select a template to match different environments',
                'default'
            )->addOption(
                'program',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Service program name',
                'nginx'
            )->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Service program user',
                ''
            )->addOption(
                'preview',
                null,
                InputOption::VALUE_NONE,
                'Show run command line information'
            )->addOption(
                'with-root',
                null,
                InputOption::VALUE_NONE,
                'Command with prefix sudo'
            )->addOption(
                'without-root',
                null,
                InputOption::VALUE_NONE,
                'Command without prefix sudo'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $params = $this->configure->get('nginx', ['root' => false]);
        $template = $input->getOption('template');
        if ($input->getOption('with-root')) {
            $params['root'] = true;
        } elseif ($input->getOption('without-root')) {
            $params['root'] = false;
        }

        if ($input->getOption('program')) {
            $params['program'] = $input->getOption('program');
        }
        if ($input->getOption('user')) {
            $params['user'] = $input->getOption('user');
        }

        $service = new Service($this->configure->get("templates.$template"), $params);

        if ($input->getOption('preview')) {
            $preview = $service->getShellCommand($input->getArgument('signal'));
            $this->io->writeln("<info>$preview</info>");
            return;
        }

        $out = $service->runShellCommand($input->getArgument('signal'));
        if ($input->getOption('echo')) {
            $output->write($out);
        }
    }
}