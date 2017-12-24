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
 * Class Manager
 *
 * @package Panlatent\SiteCli\Site
 */
class Manager extends Group
{
    /**
     * @var string
     */
    protected $enabled;

    /**
     * Manager constructor.
     *
     * @param string $available
     * @param string $enabled
     * @throws \Panlatent\SiteCli\Site\NotFoundException
     */
    public function __construct($available, $enabled)
    {
        if ( ! is_dir($available)) {
            throw new NotFoundException('site-available directory does not exist');
        } elseif ( ! is_dir($enabled)) {
            throw new NotFoundException('site-enabled directory does not exist');
        }
        $this->enabled = $enabled;
        parent::__construct(null, $available);
    }

    final function getName()
    {
        return '';
    }

    final function getPrettyName()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getAvailable()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return int
     */
    public function getEnableSiteCount()
    {
        $count = 0;
        foreach ($this->filter()->sites() as $site) {
            if ($site->isEnable()) {
                $count += 1;
            }
        }

        return $count;
    }

    /**
     * @return array
     */
    public function getLostSymbolicLinkEnables()
    {
        $enables = [];
        $finder = new Finder();
        $finder->files()->depth(0)->in($this->enabled);
        foreach ($finder as $file) {
            if ($file->isLink() && ! is_file($file->getLinkTarget())) {
                $enables[] = $file->getPathname();
            }
        }

        return $enables;
    }
}