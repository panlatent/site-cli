<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Site;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class Linker
{
    /**
     * @var Filesystem
     */
    static protected $filesystem;
    /**
     * @var Site
     */
    protected $site;
    /**
     * @var string
     */
    protected $link;
    /**
     * @var
     */
    protected $connector = '_';

    public function __construct(Site $site, $link = null)
    {
        if (static::$filesystem === null) {
            static::$filesystem = $this->makeFilesystem();
        }
        $this->site = $site;
        $this->link = $link ? $link : $this->makeLink();
    }

    /**
     * @param string $link
     * @return string
     */
    public static function reflection($link)
    {
        $fs = new Filesystem;
        if (empty($path = $fs->readlink($link))) {
            throw new FileNotFoundException($link);
        }

        return $path;
    }

    public function link()
    {
        static::$filesystem->symlink($this->site->getPath(), $this->link);
    }

    public function unlink()
    {
        static::$filesystem->remove($this->link);
    }

    public function isLink()
    {
        return static::$filesystem->exists($this->link);
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string
     */
    protected function makeLink()
    {
        $filename = str_replace('/', $this->connector, $this->site->getPrettyName());
        $parents = $this->site->getParents();

        return end($parents)->getEnabled() . '/' . $filename;
     }

    protected function makeFilesystem()
    {
        return new Filesystem();
    }
}