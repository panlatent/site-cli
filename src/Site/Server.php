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
class Server
{
    /**
     * @var \Panlatent\SiteCli\Site\Site
     */
    protected $site;

    /**
     * @var array
     */
    protected $configure;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $listen = '';

    /**
     * Server constructor.
     *
     * @param \Panlatent\SiteCli\Site\Site $site
     * @param array                        $configure
     */
    public function __construct(Site $site, $configure)
    {
        $this->site = $site;
        $this->configure = $configure;

        if (isset($this->configure['server_name'])) {
            $this->name = $this->configure['server_name'];
        }
        if (isset($this->configure['listen'])) {
            $this->listen = $this->configure['listen'];
        }
    }

    /**
     * @return \Panlatent\SiteCli\Site\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return array
     */
    public function getConfigure()
    {
        return $this->configure;
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
    public function getListen()
    {
        return $this->listen;
    }
}