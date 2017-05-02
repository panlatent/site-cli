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
use Panlatent\SiteCli\Support\Vim;
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

    protected $checkLostSymbolicLink = true;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $io = new SymfonyStyle($input, $output);
            $this->config = new CliConfig();
            $this->config->loadConfigure();
        } catch (NotFoundException $e) {
            $io->writeln([
                '',
                "<error>{$e->getMessage()}</error>"
            ]);
            if ( ! $io->confirm('Create a .site-cli.yml file to your home?', true)) {
                throw $e;
            }

            if ( ! $this->createConfigureYmlFile($io)) {
                return;
            }
        }

        $this->manager = new ConfManager($this->config['site']['available'], $this->config['site']['enabled']);
        if ($this->checkLostSymbolicLink) {
            $this->checkLostSymbolicLink($output);
        }
    }

    private function createConfigureYmlFile(SymfonyStyle $io)
    {
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

        file_put_contents($filename, Yaml::dump($config));
        Vim::open($filename);

        try {
            $this->config->loadConfigure();
        } catch (ParseException $e) {
            $io->writeln("<error>Create .site.yml file failed. {$e->getMessage()}</error>");
            unlink($filename);
            return false;
        } catch (Exception $e) {
            $io->writeln("<error>Create .site.yml file failed. {$e->getMessage()}</error>");
            unlink($filename);
            return false;
        }

        return true;
    }

    private function checkLostSymbolicLink(OutputInterface $output)
    {
        if ($lostSymbolicLinkEnables = $this->manager->getLostSymbolicLinkEnables()) {
            foreach ($lostSymbolicLinkEnables as $enable) {
                $output->writeln(sprintf("<error>Warning: Symbolic link lost in \"%s\"</error>", $enable));
            }
            $output->writeln('<comment>Note:</comment> Run <info>disable</info> <comment>--clear-lost</comment> will remove them');
            $output->writeln('');
        }
    }
}