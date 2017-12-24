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

class Filter extends ArrayIterator
{
    /**
     * Filter constructor.
     *
     * @param array $repos
     */
    public function __construct($repos)
    {
        parent::__construct($repos);
    }

    public function children()
    {
        $children = [];
        $this->traversal(function (Node $repo) use (&$children) {
            $children[] = $repo;
        });

        return new static($children);
    }

    public function filter($callback)
    {
        $repos = [];
        $this->traversal(function (Node $repo) use (&$repos, $callback) {
            if (call_user_func($callback, $repo)) {
                $repos[] = $repo;
            }
        });

        return new static($repos);
    }

    public function names($name)
    {
        $names = [];
        $this->traversal(function (Node $repo) use ($name, &$names) {
            if ($repo->getName() === $name) {
                $names[] = $repo;
            }
        });

        return new static($names);
    }

    /**
     * @param string $name
     * @return bool|Group
     */
    public function group($name)
    {
        foreach ($this->groups() as $group) {
            if ($group->getName() == $name) {
                return $group;
            }
        }

        return false;
    }

    /**
     * Get all groups.
     *
     * @return static|Group[]
     */
    public function groups()
    {
        $groups = [];
        $this->traversal(function (Node $repo) use (&$groups) {
            if ($repo instanceof Group) {
                $groups[$repo->getPrettyName()] = $repo;
            }
        });

        return new static($groups);
    }

    /**
     * @param string $name
     * @return bool|Site
     */
    public function site($name)
    {
        foreach ($this->sites() as $site) {
            if ($site->getName() == $name) {
                return $site;
            }
        }

        return false;
    }

    /**
     * Get all sites.
     *
     * @return static|Site[]
     */
    public function sites()
    {
        $sites = [];
        $this->traversal(function (Node $repo) use (&$sites) {
            if ($repo instanceof Site) {
                $sites[$repo->getPrettyName()] = $repo;
            }
        });

        return new static($sites);
    }

    /**
     * @return Server[]
     */
    public function servers()
    {
        $servers = [];
        $this->traversal(function ($repo) use (&$servers) {
            if ($repo instanceof Server) {
                $servers[$repo->getPrettyName()] = $repo;
            }
        });

        return $servers;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() == 0;
    }

    /**
     * Get enable site count.
     *
     * @return int
     */
    public function enableCount()
    {
        $count = 0;
        $this->traversal(function ($repo) use (&$count) {
            if ($repo instanceof Site && $repo->isEnable()) {
                ++$count;
            }
        });

        return $count;
    }

    protected function traversal($callable, Node $repo = null)
    {
        if ($repo === null) {
            $repo = $this;
        }
        if ($repo !== $this) {
            call_user_func($callable, $repo);
        }
        if (count($repo) !== 0) {
            foreach ($repo as $item) {
                $this->traversal($callable, $item);
            }
        }
    }
}