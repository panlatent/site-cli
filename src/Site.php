<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli;

use Symfony\Component\Filesystem\Filesystem;

class Site
{
    const CONNECTOR = '_';

    protected $group;

    protected $name;

    protected $path;

    protected $connector = self::CONNECTOR;

    private $servers = [];

    public function __construct(SiteGroup $group, $name, $path)
    {
        $this->group = $group;
        $this->name = $name;
        $this->path = $path;
        $this->parser();
    }

    /**
     * @return \Panlatent\SiteCli\SiteGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function getName()
    {
        return $this->name;
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
        return $this->group->getManager()->getEnabled() . $this->group->getName() . $this->connector . $this->name;
    }

    protected function parser()
    {

    }
}