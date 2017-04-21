<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\CliConfig;
use Panlatent\SiteCli\ConfManager;
use Panlatent\SiteCli\Exception;
use Panlatent\SiteCli\NotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var CliConfig
     */
    protected $config;

    /**
     * @var ConfManager
     */
    protected $manager;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->config = new CliConfig();
            $this->config->loadConfigure();
        } catch (NotFoundException $e) {
            $io = new SymfonyStyle($input, $output);
            $io->writeln([
                '',
                "<error>{$e->getMessage()}</error>"
            ]);
            if ( ! $io->confirm('Create a .site.yml file to your home?', true)) {
                throw $e;
            }

            $config = Yaml::parse(file_get_contents($this->config->getDefaultConfigure()));
            $filename = $this->config->getHome() . '.site-cli.yml';

            $location = $this->config->locate();
            $path = $io->choice('Which of the following is your nginx configure path:', array_merge(
                [0 => 'skip'],
                $location
            ), 'skip');
            if ($path !== 'skip') {
                $config['site']['available'] = $path . 'sites-available';
                $config['site']['enabled'] = $path . 'sites-enabled';
            }

            $descriptors = [
                ['file', '/dev/tty', 'r'],
                ['file', '/dev/tty', 'w'],
                ['file', '/dev/tty', 'w'],
            ];

            file_put_contents($filename, Yaml::dump($config));
            $process = proc_open('vim ' . $filename, $descriptors, $pipes);
            if (is_resource($process)) {
                while (true) {
                    if (proc_get_status($process)['running'] == false) {
                        break;
                    }
                    usleep(100);
                }
            }

            try {
                $this->config->loadConfigure();
            } catch (ParseException $e) {
                $output->writeln("<error>Create .site.yml file failed. {$e->getMessage()}</error>");
                unlink($filename);
                return;
            } catch (Exception $e) {
                $output->writeln("<error>Create .site.yml file failed. {$e->getMessage()}</error>");
                unlink($filename);
                return;
            }
        }

        $this->manager = new ConfManager($this->config['site']['available'], $this->config['site']['enabled']);
        foreach ($this->manager->getLostSymbolicLinkEnables() as $enable) {
            $output->writeln('');
            $output->writeln(sprintf("<error>Warning: Symbolic link lost in \"%s\"</error>", $enable));
            $output->writeln('');
        }
    }
}