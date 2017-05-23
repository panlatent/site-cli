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
            ->addOption('dump-complete', null, InputOption::VALUE_NONE);
    }

    public function register(Configure $configure)
    {
        $this->configure = $configure;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dump-complete')) {
            $this->dumpCompleteFile();
            return true;
        }

        $config = $this->configure->all();
        $filename = Util::home() . '/.site-cli.yml';

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

    private function dumpCompleteFile()
    {
        $program = $_SERVER['argv'][0];
        exec($program .  ' _completion --generate-hook', $output);
        $completion = implode("\n", $output);
        $content = file_get_contents(__DIR__ . '/../../build/completion.bash.template');
        $content = str_replace('{% complete %}', $completion, $content);
        file_put_contents(Util::project() . '/completion.bash', $content);
    }
}