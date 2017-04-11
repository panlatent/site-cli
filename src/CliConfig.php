<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli;

use Panlatent\Boost\Storage;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class CliConfig extends Storage
{
    protected $home;

    protected $user;

    protected $cwd;

    public function __construct($storage = [])
    {
        parent::__construct($storage);

        $this->home = getenv('HOME') . DIRECTORY_SEPARATOR;
        $this->user = getenv('USER'). DIRECTORY_SEPARATOR;
        $this->cwd = getcwd(). DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getCwd()
    {
        return $this->cwd;
    }

    public function getDefaultConfigure()
    {
        return __DIR__ . '/../.site-cli.yml';
    }

    public function loadConfigure()
    {
        $finder = new Finder();
        $finder->files()->ignoreDotFiles(false)->name('.site-cli.yml')->depth(0)->in([
            $this->home,
            $this->cwd,
        ]);
        if ( ! count($finder)) {
            throw new NotFoundException('Not found .site-cli.yml from user home or current path');
        }
        foreach ($finder as $file) {
            $this->storage = array_merge($this->storage, Yaml::parse($file->getContents()));
        }

        $this->requiredHandler();
    }

    protected function requiredHandler()
    {
        if (empty($this->storage['site']['available'])) {
            throw new Exception('Not found site.available setting in .site-cli.yml');
        }
        $this->storage['site']['available'] = $this->amendPath($this->storage['site']['available']);

        if (empty($this->storage['site']['enabled'])) {
            throw new Exception('Not found site.enabled setting in .site-cli.yml');
        }
        $this->storage['site']['enabled'] = $this->amendPath($this->storage['site']['enabled']);
    }

    protected function amendPath($path)
    {
        if (strncmp($path, '~', 1) === 0) {
            $path = substr($this->home, 0, -1) . substr($path, 1);
        }

        return rtrim($path, '/') . DIRECTORY_SEPARATOR;
    }
}