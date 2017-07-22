<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Control;

use InvalidArgumentException;
use Panlatent\SiteCli\Support\Util;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Service
{
    /**
     * @var array
     */
    protected $templates;

    /**
     * @var bool
     */
    protected $root = false;

    /**
     * @var string
     */
    protected $user = '';

    /**
     * @var string
     */
    protected $program = 'nginx';

    public function __construct($templates, $params = [])
    {
        $this->templates = $templates;

        foreach ($params as $property => $value) {
            if (isset($this->$property)) {
                $this->$property = $value;
            }
        }
    }

    public function start()
    {
        $this->runShellCommand('start');
    }

    public function stop()
    {
        $this->runShellCommand('stop');
    }

    public function restart()
    {
        $this->runShellCommand('restart');
    }

    public function reload()
    {
        $this->runShellCommand('reload');
    }

    public function status()
    {
        $this->runShellCommand('status');
    }

    public function getShellCommand($template)
    {
        if ( ! isset($this->templates[$template])) {
            throw new InvalidArgumentException("Undefined signal");
        }

        return $this->getTemplateSegment($this->templates[$template]);
    }

    public function runShellCommand($template)
    {
        $process = new Process($this->getShellCommand($template));
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    protected function getTemplateSegment($template)
    {
        $withoutUser = (empty($this->user) || $this->user == Util::user());
        $withoutUser or $this->root = false;

        $template = preg_replace_callback('#%(\w+)%#', function($match) {
            $attribute = $match[1];
            if ( ! isset($this->$attribute)) {
                throw new InvalidArgumentException("Undefined template segment attribute: $attribute");
            }
            return $this->$attribute;
        }, $template);

        $template = preg_replace_callback('#%((\w+):([^%]+))%#', function($match) {
            $attribute = $match[2];
            $true = $match[3];
            if (isset($this->$attribute) && $this->$attribute) {
                return $true;
            }
            return '';
        }, $template);

        $template = trim($template);
        if ($withoutUser) {
            return trim($template);
        }

        return "su - $this->user -c " . $template;
    }
}