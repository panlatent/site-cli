<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Nginx;

use Panlatent\Boost\BStack;
use Panlatent\Boost\Storage;

/**
 * Class ConfParser
 *
 * @package Panlatent\SiteCli\Nginx
 */
class ConfParser extends Storage
{
    const STATUS_EMPTY = 0;
    const STATUS_KEY = 1;
    const STATUS_VALUE = 2;

    /**
     * ConfParser constructor.
     *
     * @param string $content
     * @throws \Panlatent\Boost\Exception
     */
    public function __construct($content)
    {
        parent::__construct();
        $this->storage = $this->parser($content);
        $this->clip();
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->storage;
    }

    /**
     * @param string $content
     * @return array
     */
    protected function parser($content)
    {
        $stack = new BStack();
        $status = self::STATUS_EMPTY;
        $current = [];
        $key = '';
        $value = '';

        foreach ($this->scanner($content) as $char) {
            if ($status === self::STATUS_EMPTY && ($char == ' ' || $char == "\t" || $char == "\n" || $char == "\r")) {
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

    /**
     * Clean up some useless space.
     */
    protected function clip()
    {
        array_walk_recursive($this->storage, function(&$value) {
            $value = trim(preg_replace('#^\s*"(.*)"\s*$#', '\1', $value), " \t");
        });
    }

    /**
     * @param string $content
     * @return \Generator
     */
    protected function scanner($content)
    {
        $length = strlen($content);
        for ($i = 0; $i < $length; ++$i) {
            yield $content[$i];
        }
    }
}