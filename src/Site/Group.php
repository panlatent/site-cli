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

/**
 * Class Group
 *
 * @package Panlatent\SiteCli\Site
 */
class Group
{
    /**
     * @var \Panlatent\SiteCli\Site\Manager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \Panlatent\SiteCli\Site\Site[]
     */
    private $sites = [];

    /**
     * Group constructor.
     *
     * @param \Panlatent\SiteCli\Site\Manager $manager
     * @param string                          $name
     * @param string                          $path
     */
    public function __construct(Manager $manager, $name, $path)
    {
        $this->manager = $manager;
        $this->name = $name;
        $this->path = $path;
        $this->parser();
    }

    /**
     * @return int
     */
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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return int
     */
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

    /**
     * @param string $name
     * @return bool|\Panlatent\SiteCli\Site\Site
     */
    public function getSite($name)
    {
        foreach ($this->sites as $siteName => $site) {
            if ($siteName == $name) {
                return $site;
            }
        }

        return false;
    }

    /**
     * @return \Panlatent\SiteCli\Site\Site[]
     */
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