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

/**
 * Class Site
 *
 * @package Panlatent\SiteCli\Site
 */
class Site extends Node
{
    /**
     * @var Linker
     */
    protected $linker;

    /**
     * Site constructor.
     *
     * @param  string     $name
     * @param  string     $path
     * @param  Group|null $parent
     */
    public function __construct($name, $path, Group $parent = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->parent = $parent;
        $this->linker = new Linker($this);
        $this->reload();
    }

    public function reload()
    {
        $content = file_get_contents($this->path);
        $parser = new ConfParser($content);
        foreach ($parser as $key => $value) {
            if ($key == 'server') {
                if ( ! is_numeric(implode('', array_keys($value)))) {
                    $this[] = $this->makeServer($value);
                } else {
                    foreach ($value as $server) {
                        $this[] = $this->makeServer($server);
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
        return $this->parent;
    }

    /**
     * @return \Panlatent\SiteCli\Site\Server[]
     */
    public function getServers()
    {
        return $this->getArrayCopy();
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->linker->isLink();
    }

    /**
     * Enable the site
     */
    public function enable()
    {
        $this->linker->link();
    }

    /**
     * Disable the site
     */
    public function disable()
    {
        $this->linker->unlink();
    }

    protected function makeServer($params)
    {
        return new Server(null, $this->path, $params, $this);
    }
}