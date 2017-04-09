<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Support;

use Panlatent\Boost\BStack;
use Panlatent\Boost\Storage;

class NginxConfParser extends Storage
{
    const STATUS_EMPTY = 0;
    const STATUS_KEY = 1;
    const STATUS_VALUE = 2;

    protected $isWithInclude;

    public function __construct($content, $withInclude = false)
    {
        parent::__construct();
        $this->isWithInclude = $withInclude;
        $this->storage = $this->parser($content);
        $this->clip();
        if ($withInclude) {
            $this->searchInclude();
        }
    }

    public function all()
    {
        return $this->storage;
    }

    public function isWithInclude()
    {
        return $this->isWithInclude;
    }

    protected function parser($content)
    {
        $stack = new BStack();
        $status = self::STATUS_EMPTY;
        $current = [];
        $key = '';
        $value = '';

        foreach ($this->scanner($content) as $char) {
            if ($status === self::STATUS_EMPTY && ($char == ' ' || $char == "\t" || $char == "\n")) {
                continue;
            } elseif ($status === self::STATUS_KEY && ($char == ' ' || $char == "\t" || $char == "\n")) {
                $status = self::STATUS_VALUE;
                continue;
            } elseif ($status === self::STATUS_VALUE && ($char == ';')) {
                $current[$key] = $value;
                $key = '';
                $value = '';
                $status = self::STATUS_EMPTY;
                continue;
            }

            if ($char == '{') {
                $stack->push([$key, $current]);
                $key = '';
                $value = '';
                $current = [];
                $status = self::STATUS_EMPTY;
                continue;
            } elseif ($char == '}') {
                $node = $stack->pop();
                $key = $node[0];
                if (isset($node[1][$key])) {
                    if ( ! $stack->isEmpty()) {
                        if ( ! is_array($node[1][$key])) {
                            $node[1][$key] = [$node[1][$key]];
                        }
                        $node[1][$key][] = $current;
                    } else { // Top: server
                        if ( ! is_numeric(implode('', array_keys($node[1][$key])))) {
                            $node[1][$key] = [$node[1][$key]];
                        }
                        $node[1][$key][] = $current;
                    }
                } else {
                    $node[1][$key] = $current;
                }
                $current = $node[1];
                $key = '';
                $value = '';
                continue;
            }

            if ($status === self::STATUS_EMPTY) {
                $status = self::STATUS_KEY;
                $key .= $char;
                continue;
            } elseif ($status === self::STATUS_KEY) {
                $key .= $char;
                continue;
            } elseif ($status === self::STATUS_VALUE) {
                $value .= $char;
                continue;
            }
        }

        return $current;
    }

    protected function clip()
    {
        array_walk_recursive($this->storage, function(&$value) {
            $value = trim(preg_replace('#^\s*"(.*)"\s*$#', '\1', $value), " \t");
        });
    }

    protected function searchInclude()
    {
        array_walk_recursive($this->storage, function($value,  $key) {
            if ($key == 'include') {
                // @todo
            }
        });
    }

    protected function scanner($content)
    {
        $length = strlen($content);
        for ($i = 0; $i < $length; ++$i) {
            yield $content[$i];
        }
    }
}