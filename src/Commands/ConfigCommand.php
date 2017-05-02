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
use Panlatent\SiteCli\Exception;
use Panlatent\SiteCli\Support\Vim;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

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
        switch ($input->getArgument('target')) {
            case 'init':
                $this->config = new CliConfig();
                return $this->createConfigureYmlFile(new SymfonyStyle($input, $output));
            case 'dump-complete':
                parent::execute($input, $output);
                return $this->createDumpCompleteFile();
            default:
                $output->writeln('<error>Not found config target</error>');
                return false;
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