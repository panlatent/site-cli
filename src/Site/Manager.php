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
 * Class Manager
 *
 * @package Panlatent\SiteCli\Site
 */
class Manager
{
    /**
     * @var string
     */
    protected $available;

    /**
     * @var string
     */
    protected $enabled;

    /**
     * @var \Panlatent\SiteCli\Site\Group[]
     */
    protected $groups = [];

    /**
     * Manager constructor.
     *
     * @param string $available
     * @param string $enabled
     * @throws \Panlatent\SiteCli\Site\NotFoundException
     */
    public function __construct($available, $enabled)
    {
        if ( ! is_dir($available)) {
            throw new NotFoundException('site-available directory does not exist');
        } elseif ( ! is_dir($enabled)) {
            throw new NotFoundException('site-enabled directory does not exist');
        }

        $this->available = $available . DIRECTORY_SEPARATOR;
        $this->enabled = $enabled . DIRECTORY_SEPARATOR;

        $finder = new Finder();
        $finder->directories()->depth(0)->in($this->available); // Find group
        foreach ($finder as $directory) {
            $name = $directory->getFilename();
            $this->groups[$name] = new Group($this, $name, $directory->getPathname());
        }

        $finder = new Finder();
        $finder->files()->depth(0)->in($this->available); // Find unknown group
        if ($finder->count()) {
            $this->groups['@default'] = new Group($this, '@default', $this->available);
        }
    }

    /**
     * @param string $name
     * @return bool|\Panlatent\SiteCli\Site\Group
     */
    public function getGroup($name)
    {
        foreach ($this->groups as $groupName => $group) {
            if ($groupName == $name) {
                return $group;
            }
        }

        return false;
    }

    /**
     * @return \Panlatent\SiteCli\Site\Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return \Panlatent\SiteCli\Site\Site[]
     */
    public function getSites()
    {
        $sites = [];
        foreach ($this->groups as $group) {
            $sites = array_merge($sites, $group->getSites());
        }

        return $sites;
    }

    /**
     * @return \Panlatent\SiteCli\Site\Server[]
     */
    public function getServers()
    {
        $servers = [];
        foreach ($this->groups as $group) {
            foreach ($group->getSites() as $site) {
                $servers = array_merge($servers, $site->getServers());
            }
        }

        return $servers;
    }

    /**
     * @return string
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @return string
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return array
     */
    public function getLostSymbolicLinkEnables()
    {
        $enables = [];
        $finder = new Finder();
        $finder->files()->depth(0)->in($this->enabled);
        foreach ($finder as $file) {
            if ($file->isLink() && ! is_file($file->getLinkTarget())) {
                $enables[] = $file->getPathname();
            }
        }

        return $enables;
    }
}