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
use Panlatent\SiteCli\Exception;
use Panlatent\SiteCli\Support\Util;
use Panlatent\SiteCli\Support\Editor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class InitCommand extends Command
{
    /**
     * @var \Panlatent\SiteCli\Configure
     */
    protected $configure;

    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Init site-cli settings')
            ->addOption(
                'dump-completion',
                null,
                InputOption::VALUE_NONE,
                'Dump shell completion script contents'
            )->addOption(
                'output',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Output file path'
            )->addOption(
                'command',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Command name'
            )->addOption(
                'program',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Program name'
            );
    }

    public function register(Configure $configure)
    {
        $this->configure = $configure;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dump-completion')) {
            $result = $this->dumpCompletion(
                $input->getOption('output'),
                $input->getOption('command'),
                $input->getOption('program'));
            if (is_string($result)) {
                $output->write($result);
            }
            return true;
        }

        $config = $this->configure->all();
        $filename = $input->getOption('output') ? $input->getOption('output') : Util::home() . '/.site-cli.yml';

        $location = $this->locate();
        $path = $this->io->choice('Which of the following is your nginx configure path:', array_merge(
            [0 => 'skip'],
            $location
        ), 'skip');
        if ($path !== 'skip') {
            $config['available'] = $path . '/sites-available';
            $config['enabled'] = $path . '/sites-enabled';
        }

        file_put_contents($filename, Yaml::dump($config, 8));
        Editor::vim($filename);

        try {
            new Configure($filename);
        } catch (ParseException $e) {
            $this->io->writeln("<error>Create .site.yml file failed. {$e->getMessage()}</error>");
            unlink($filename);
            return false;
        } catch (Exception $e) {
            $this->io->writeln("<error>Create .site.yml file failed. {$e->getMessage()}</error>");
            unlink($filename);
            return false;
        }

        return true;
    }

    private function locate()
    {
        $probables = [];
        foreach ($this->configure['nginx']['search'] as $path) {
            $path = Util::realPath($path);
            if (is_dir($path)) {
                $probables[] = $path;
            }
        }

        return $probables;
    }

    private function dumpCompletion($output, $command, $program)
    {
        if (empty($command)) {
            $command = $_SERVER['argv'][0];
        }
        if (empty($program)) {
            $program = $_SERVER['argv'][0];
        }
        $content = file_get_contents(__DIR__ . '/../../build/completion.bash');
        $content = str_replace('{% command %}', $command, $content);
        $content = str_replace('{% program %}', $program, $content);
        if ( ! empty($output)) {
            $status = @file_put_contents($output, $content);
            return (bool)$status;
        }

        return $content;
    }
}