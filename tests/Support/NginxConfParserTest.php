<?php
/**
 * SiteCLI - Help you manage Nginx local development configuration
 *
 * @author  panlatent@gmail.com
 * @link    https://github.com/panlatent/site-cli
 * @license https://opensource.org/licenses/MIT
 */

namespace Tests\Support;

use Panlatent\SiteCli\Nginx\ConfParser;

class NginxConfParserTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $conf = new ConfParser(file_get_contents(__DIR__ . '/../_data/default_site.conf'));
        $this->assertEquals([
            'server' =>
                [
                    'listen'      => '80',
                    'server_name' => 'localhost 127.0.0.1',
                    'index'       => 'index.php index.html index.htm',
                    'root'        => '/var/www/html',
                    'location'    =>
                        [
                            'try_files' => '$uri $uri/ /index.php',
                            0           =>
                                [
                                    'alias' => '/assets/css',
                                ],
                            1           =>
                                [
                                    'fastcgi_pass'            => '127.0.0.1:9000',
                                    'fastcgi_index'           => 'index.php',
                                    'fastcgi_split_path_info' => '^((?U).+\\.php)(/?.+)$',
                                    'fastcgi_param'           => 'PATH_TRANSLATED  $document_root$fastcgi_path_info',
                                    'include'                 => 'fastcgi_params',
                                ],
                        ],
                ],
        ], $conf->all());
    }
}
