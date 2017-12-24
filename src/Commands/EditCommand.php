<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Commands;

use Panlatent\SiteCli\Service\Reloadable;
use Panlatent\SiteCli\Service\ReloadTrait;
use Panlatent\SiteCli\Site\NotFoundException;
use Panlatent\SiteCli\Support\Editor;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommand extends Command implements Reloadable
{
    use ReloadTrait;

    protected function configure()
    {
        $this->setName('edit')
            ->setDescription('Edit site configuration using editor')
            ->addArgument(
                'site',
                InputArgument::OPTIONAL,
                'Site name'
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
        if (false === ($pos = strpos($input->getArgument('site'), '/'))) {
            $groupName = $input->getArgument('site');
            $siteName = '';
        } else {
            $groupName = substr($input->getArgument('site'), 0, $pos);
            $siteName = substr($input->getArgument('site'), $pos + 1);
        }

        $editor = $input->getOption('editor');
        if (false === ($group = $this->getManager()->filter()->group($groupName))) {
            throw new NotFoundException("Not found site group \"$groupName\"");
        }

        if (empty($siteName)) {
            $this->openEditor($editor, $group->getPath());
            $this->disableServiceReload();
            return;
        }

        if (false === ($site = $group->filter()->site($siteName))) {
            throw new NotFoundException("Not found site \"$siteName\" in $groupName group");
        }

        $sha1Old = sha1(file_get_contents($site->getPath()));
        $this->openEditor($editor, $site->getPath());
        $sha1New = sha1(file_get_contents($site->getPath()));
        if ($sha1Old == $sha1New) {
            $this->disableServiceReload();
        }
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

    protected function getOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName == 'editor') {
            return ['vim', 'sublime'];
        }

        return parent::completeOptionValues($optionName, $context);
    }
}