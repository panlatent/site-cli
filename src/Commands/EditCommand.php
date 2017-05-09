<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Site\Manager;
use Panlatent\SiteCli\Site\NotFoundException;
use Panlatent\SiteCli\Support\Editor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommand extends Command
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
        $this->setName('edit')
            ->setDescription('Edit site configuration using editor')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'Group name'
            )
            ->addArgument(
                'site',
                InputArgument::OPTIONAL,
                'Site name in the group'
            )
            ->addOption(
                'editor',
                'e',
                InputOption::VALUE_REQUIRED,
                'use editor',
                'vim'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupName = $input->getArgument('group');
        $siteName = $input->getArgument('site');
        $editor = $input->getOption('editor');
        if (false === ($group = $this->manager->getGroup($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        if (empty($siteName)) {
            $this->openEditor($editor, $group->getPath());
            return;
        }

        if (false === ($site = $group->getSite($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }

        $this->openEditor($editor, $site->getPath());
    }

    protected function openEditor($editor, $path)
    {
        switch ($editor) {
            case 'vi':
            case 'vim':
                Editor::vim($path);
                break;
            case 'subl':
            case 'sublime':
                Editor::sublime($path);
        }
    }
}