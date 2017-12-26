<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Service\Control;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
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
                InputOption::VALUE_REQUIRED,
                'Service program user'
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
        $params = $this->getConfigure()->get('service', ['root' => false]);
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

        $service = new Control($this->getConfigure()->get("templates.$template"), $params);

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

    protected function getArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'signal') {
            $templateName = $this->getConfigure()->get('nginx.template', 'default');
            $template = $this->getConfigure()->get("templates.$templateName", []);

            return array_keys($template);
        }

        return parent::getArgumentValues($argumentName, $context);
    }

    protected function getOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName == 'template') {
            $templates = $this->getConfigure()->get("templates", []);
            return array_keys($templates);
        } elseif ($optionName == 'user') {
            $users = [];
            if ($list = @file('/etc/passwd')) {
                foreach ($list as $item) {
                    $pos = strpos($item, ':');
                    $users[] = substr($item, 0, $pos);
                }
            }
            return $users;
        }

        return parent::getOptionValues($optionName, $context);
    }
}