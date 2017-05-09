<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Panlatent\SiteCli\Support;

use Panlatent\SiteCli\Exception;

class Editor
{
    protected static $descriptors = [
        ['file', '/dev/tty', 'r'],
        ['file', '/dev/tty', 'w'],
        ['file', '/dev/tty', 'w'],
    ];

    public static function vim($filename)
    {
        $process = proc_open('vim ' . escapeshellarg($filename), static::$descriptors, $pipes);
        if ( ! is_resource($process)) {
            throw new Exception('Open vim failed');
        }

        while (true) {
            if (proc_get_status($process)['running'] == false) {
                break;
            }
            usleep(100);
        }
    }

    public static function sublime($filename)
    {
        exec('subl ' . escapeshellarg($filename) . ' >/dev/null 2>&1');
    }
}