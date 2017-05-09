<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Site;

use Symfony\Component\Finder\Finder;

class Group
{
    protected $manager;

    protected $name;

    protected $path;

    /**
     * @var \Panlatent\SiteCli\Site\Site[]
     */
    private $sites = [];

    public function __construct(Manager $manager, $name, $path)
    {
        $this->manager = $manager;
        $this->name = $name;
        $this->path = $path;
        $this->parser();
    }

    public function count()
    {
        return count($this->sites);
    }

    /**
     * @return \Panlatent\SiteCli\Site\Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getEnableSiteCount()
    {
        $count = 0;
        foreach ($this->sites as $site) {
            if ($site->isEnable()) {
                $count += 1;
            }
        }

        return $count;
    }

    public function getSite($name)
    {
        foreach ($this->sites as $siteName => $site) {
            if ($siteName == $name) {
                return $site;
            }
        }

        return false;
    }

    public function getSites()
    {
        return $this->sites;
    }

    protected function parser()
    {
        $finder = new Finder();
        $finder->files()->depth(0)->in($this->path);
        foreach ($finder as $file) {
            $name = $file->getFilename();
            $this->sites[$name] = new Site($this, $name, $file->getPathname());
        }
    }
}