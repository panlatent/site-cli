<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Site;

use ArrayIterator;
use InvalidArgumentException;

abstract class Node extends ArrayIterator implements NodeInterface
{
    const PRETTY_SEPARATOR = '/';
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var \Panlatent\SiteCli\Site\NodeInterface|null
     */
    protected $parent;

    public function getName()
    {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Undefined repository name');
        }

        return $this->name;
    }

    public function getPrettyName()
    {
        $parentPrettyName = $this->parent ? $this->parent->getPrettyName() : '';

        return ltrim($parentPrettyName . self::PRETTY_SEPARATOR . $this->name, self::PRETTY_SEPARATOR);
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getParents()
    {
        $parents = [];
        for ($parent = $this->parent; $parent; $parent = $parent->getParent()) {
            $parents[] = $parent;
        }

        return $parents;
    }

    public function filter()
    {
        return new Filter($this->getArrayCopy());
    }
}