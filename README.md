Site CLI
========
[![Build Status](https://travis-ci.org/panlatent/site-cli.svg)](https://travis-ci.org/panlatent/site-cli)
[![Latest Stable Version](https://poser.pugx.org/panlatent/site-cli/v/stable.svg)](https://packagist.org/packages/panlatent/site-cli) 
[![Total Downloads](https://poser.pugx.org/panlatent/site-cli/downloads.svg)](https://packagist.org/packages/panlatent/site-cli) 
[![Latest Unstable Version](https://poser.pugx.org/panlatent/site-cli/v/unstable.svg)](https://packagist.org/packages/panlatent/site-cli) 
[![License](https://poser.pugx.org/panlatent/site-cli/license.svg)](https://packagist.org/packages/panlatent/site-cli)

Help you manage Nginx local development configuration

Site CLI 是一个帮助你管理和切换 Nginx 本地开发环境配置文件的命令行工具。

Features
--------

+ Site Grouping
+ List Site Information
+ Quick Switching Group/Site/Server
+ Quick Build From Template

Install
-------

1. Download the project

+ Composer
  ```bash
  composer require panlatent/site-cli
  ```
+ Phar
  
  [Download](https://github.com/panlatent/site-cli/releases)

2. Create Nginx conf directory

```bash
mkdir ~/etc/nginx/
mkdir ~/etc/nginx/sites-available
mkdir ~/etc/nginx/sites-enabled
```

Include `~/etc/nginx/sites-enabled` in your `nginx.conf`

3. Configure .site-cli.yml to your home directory.

```yaml
site:
  available: ~/etc/nginx/sites-available
  enabled: ~/etc/nginx/sites-enabled
```

4. Optional
```bash
ln -s site-cli-path /usr/local/bin/site-cli # Alias site
chmod +x /usr/local/bin/site-cli
```
Or
```bash
mv site-cli.phar /usr/local/bin/site-cli # Alias site
chmod +x /usr/local/bin/site-cli
```

Usage
-----

```bash
$ site-cli [command] [argment]
```

    Options:
      -h, --help            Display this help message
      -q, --quiet           Do not output any message
      -V, --version         Display this application version
          --ansi            Force ANSI output
          --no-ansi         Disable ANSI output
      -n, --no-interaction  Do not ask any interactive question
      -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
    
    Available commands:
      config         Setting your .site-cli.yml
      help           Displays help for a command
      list           Lists commands
     group
      group:disable  Enable a group sites
      group:enable   Enable a group sites
      group:list     [groups] Lists all site groups
     site
      site:disable   [disable] Disable a site in a group
      site:enable    [enable] Enable a site in a group
      site:list      [sites] Lists all sites in a group


License
-------

The Site CLI is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).