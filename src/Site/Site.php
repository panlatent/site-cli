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

class Site
{
    const CONNECTOR = '_';

    protected $group;

    protected $name;

    protected $path;

    protected $connector = self::CONNECTOR;

    private $servers = [];

    public function __construct(Group $group, $name, $path)
    {
        $this->group = $group;
        $this->name = $name;
        $this->path = $path;
        $this->parser();
    }

    /**
     * @return \Panlatent\SiteCli\Site\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function count()
    {
        return count($this->servers);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getServers()
    {
        return $this->servers;
    }

    public function isEnable()
    {
        return is_file($this->getEnableFilename());
    }

    public function enable()
    {
        $fs = new Filesystem();
        $fs->symlink($this->path, $this->getEnableFilename());
    }

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

    protected function getEnableFilename()
    {
        if (0 === strncmp($this->group->getName(), '@', 1)) {
            return $this->group->getManager()->getEnabled() . $this->name;
        }

        return $this->group->getManager()->getEnabled() . $this->group->getName() . $this->connector . $this->name;
    }

    protected function parser()
    {
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
}