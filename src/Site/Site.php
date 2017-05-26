<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Site;

use Panlatent\SiteCli\Nginx\ConfParser;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Site
 *
 * @package Panlatent\SiteCli\Site
 */
class Site
{
    /**
     * Site symbol link file connector.
     */
    const CONNECTOR = '_';

    /**
     * @var \Panlatent\SiteCli\Site\Group
     */
    protected $group;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $connector = self::CONNECTOR;

    /**
     * @var \Panlatent\SiteCli\Site\Server[]
     */
    private $servers = [];

    /**
     * Site constructor.
     *
     * @param \Panlatent\SiteCli\Site\Group $group
     * @param  string                       $name
     * @param  string                       $path
     */
    public function __construct(Group $group, $name, $path)
    {
        $this->group = $group;
        $this->name = $name;
        $this->path = $path;

        $content = file_get_contents($this->path);
        $parser = new ConfParser($content);
        foreach ($parser as $key => $value) {
            if ($key == 'server') {
                if ( ! is_numeric(implode('', array_keys($value)))) {
                    $this->servers[] = new Server($this, $value);
                } else {
                    foreach ($value as $server) {
                        $this->servers[] = new Server($this, $server);
                    }
                }
            }
        }
    }

    /**
     * @return \Panlatent\SiteCli\Site\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->servers);
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
     * @return \Panlatent\SiteCli\Site\Server[]
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return is_file($this->getEnableFilename());
    }

    /**
     * Enable the site
     */
    public function enable()
    {
        $fs = new Filesystem();
        $fs->symlink($this->path, $this->getEnableFilename());
    }

    /**
     * Disable the site
     */
    public function disable()
    {
        $fs = new Filesystem();
        $fs->remove($this->getEnableFilename());
    }

    /**
     * @param string $connector
     */
    public function setConnector($connector)
    {
        $this->connector = $connector;
    }

    /**
     * @return string
     */
    protected function getEnableFilename()
    {
        if (0 === strncmp($this->group->getName(), '@', 1)) {
            return $this->group->getManager()->getEnabled() . $this->name;
        }

        return $this->group->getManager()->getEnabled() . $this->group->getName() . $this->connector . $this->name;
    }
}