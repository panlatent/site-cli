<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Service;

trait ReloadTrait
{
    protected $reload = true;

    public function canReloadService()
    {
        return $this->reload;
    }

    public function enableServiceReload()
    {
        $this->reload = true;
    }

    public function disableServiceReload()
    {
        $this->reload = false;
    }
}