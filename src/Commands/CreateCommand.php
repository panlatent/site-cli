<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use InvalidArgumentException;
use Panlatent\SiteCli\Site\Manager;
use Panlatent\SiteCli\Site\NotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    /**
     * @var \Panlatent\SiteCli\Site\Manager
     */
    protected $manager;

    public function register(Manager $manager)
    {
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this->setName('create')
            ->setDescription('Create a new site')
            ->addArgument('target', InputArgument::REQUIRED, 'Site name like group/site')
            ->addOption('from', 't', InputOption::VALUE_REQUIRED, 'From a exists site or template copy')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force a new site')
            ->addOption('server-name', 'a', InputOption::VALUE_REQUIRED, 'Server name')
            ->addOption('server-root', 'c', InputOption::VALUE_REQUIRED, 'server root path')
            ->addOption('server-listen', 'p', InputOption::VALUE_REQUIRED, 'Server listen port', 80);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $distFile = $this->manager->getAvailable() . $input->getArgument('target');
        $sourceContent = '';
        if ($input->getOption('from')) {
            $this->io->writeln("<info>reading from {$input->getOption('from')}</info>");
            $sourceContent = $this->getSourceContentByFrom($input->getOption('from'));
        }

        $params = [
            'server_name' => $input->getOption('server-name'),
            'root' => $input->getOption('server-root'),
            'listen' => $input->getOption('server-listen'),
        ];

        $distContent = $this->getDistContent($sourceContent, $params);
        $this->saveFile($distFile, $distContent, $input->getOption('force'));
    }

    protected function getDistContent($template, $params = [])
    {
        $pattern = implode('|', array_keys($params));
        $template = preg_replace_callback('#(' . $pattern . ')\s+(.*?);#', function($match) use($params) {
            $value = $params[$match[1]];
            if ($value == '') {
                return $match[0];
            }
            return sprintf("%s %s;", $match[1], $value);
        }, $template);

        return $template;
    }

    protected function getSourceContentByFrom($site)
    {
        if (false === ($pos = strpos($site, '/'))) {
            $groupName = '@default';
            $siteName = $site;
        } else {
            $groupName = substr($site, 0, $pos);
            $siteName = substr($site, $pos + 1);
        }

        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }
        if (false === ($site = $group->getSite($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }

        return file_get_contents($site->getPath());
    }

    protected function saveFile($filename, $content, $force = false)
    {
        if (file_exists($filename)) {
            if (is_dir($filename)) {
                throw new InvalidArgumentException('Target is a directory');
            } elseif ( ! $force) {
                throw new InvalidArgumentException('File already exists');
            }
        }

        file_put_contents($filename, $content);
    }
}