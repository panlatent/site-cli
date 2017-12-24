<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Site;

/**
 * Class Server
 *
 * @package Panlatent\SiteCli\Site
 */
class Server extends Node
{
    protected $startLine;

    protected $endLine;

    /**
     * @var array
     */
    protected $configure;


    protected $hosts = [];

    /**
     * @var array
     */
    protected $listens = [];

    protected $root;

    protected $indexTypes = [];

    /**
     * Server constructor.
     *
     * @param string $name
     * @param string $path
     * @param array  $params
     * @param Site   $parent
     */
    public function __construct($name, $path, $params = [], Site $parent = null)
    {
        if (isset($params['server_name'])) {
            $this->name = $params['server_name'];
        }
        if (isset($params['listen'])) {
            $this->listens[] = $params['listen'];
        }

        $this->parent = $parent;
        parent::__construct($params);
    }

    public function count()
    {
        return 0;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->getArrayCopy();
    }

    /**
     * @return array
     */
    public function getListens()
    {
        return $this->listens;
    }


    public function getFirstUrl()
    {
        $urls = $this->getUrls();

        return empty($urls) ? '' : $urls[0];
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        $urls = [];
        $names = preg_split('#\s+#', $this['server_name']);
        $listens = preg_split('#\s+#', $this['listen']);
        foreach ($names as $name) {
            foreach ($listens as $listen) {
                $url = $listen == 443 ? 'https://' : 'http://';
                $url .= $name;
                ($listen != 443 && $listen != 80) and $url .= ':' . $listen;
                $urls[] = $url;
            }
        }

        return $urls;
    }
}