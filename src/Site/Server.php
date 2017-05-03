<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Site;

class Server
{
    protected $site;

    protected $configure;

    protected $name = '';

    protected $listen = '';

    public function __construct(Site $site, $configure)
    {
        $this->site = $site;
        $this->configure = $configure;
        $this->parser();
    }

    /**
     * @return \Panlatent\SiteCli\Site\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return mixed
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

    protected function parser()
    {
        if (isset($this->configure['server_name'])) {
            $this->name = $this->configure['server_name'];
        }
        if (isset($this->configure['listen'])) {
            $this->listen = $this->configure['listen'];
        }
    }
}