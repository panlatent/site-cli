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
class Group extends Node
{
    /**
     * Group constructor.
     *
     * @param string             $name
     * @param string             $path
     * @param NodeInterface|null $parent
     */
    public function __construct($name, $path, NodeInterface $parent = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->parent = $parent;
        $this->reload();
    }

    public function reload()
    {
        $finder = new Finder();
        $finder->ignoreDotFiles(true)->depth(0)->in($this->path);
        foreach ($finder as $file) {
            $name = $file->getFilename();
            if ($file->isDir()) {
                $this[$name] = $this->makeGroup($name, $file->getPathname());
            } else {
                $this[$name] = $this->makeSite($name, $file->getPathname());
            }
        }
    }

    protected function makeGroup($name, $path)
    {
        return new self($name, $path, $this);
    }

    protected function makeSite($name, $path)
    {
        return new Site($name, $path, $this);
    }
}