Site CLI
========
[![Build Status](https://travis-ci.org/panlatent/site-cli.svg)](https://travis-ci.org/panlatent/site-cli)
[![Latest Stable Version](https://poser.pugx.org/panlatent/site-cli/v/stable.svg)](https://packagist.org/packages/panlatent/site-cli) 
[![Total Downloads](https://poser.pugx.org/panlatent/site-cli/downloads.svg)](https://packagist.org/packages/panlatent/site-cli) 
[![Latest Unstable Version](https://poser.pugx.org/panlatent/site-cli/v/unstable.svg)](https://packagist.org/packages/panlatent/site-cli) 
[![License](https://poser.pugx.org/panlatent/site-cli/license.svg)](https://packagist.org/packages/panlatent/site-cli)

A command-line tool that help you manage Nginx local development configuration

What's This
------------
Site CLI is a command-line tool that helps you manage and switch Nginx local development 
environment configuration files.

Site CLI makes me lazy in the development, because I don't want to use `cd` or `ln -s` or more
, but I enjoy it.

Features
--------
+ Command/Path completion
+ Site grouping
+ List site information
+ Quick switching group/site/server
+ Quick build configuration file from template

Installation
-------------
Download the library using composer:
```bash
$ composer require panlatent/site-cli
```

[Download](https://github.com/panlatent/site-cli/releases) the library using phar.

```bash
mv site-cli.phar /usr/local/bin/site-cli
chmod +x /usr/local/bin/site-cli
```

Run `init` command wiil create a .site-cli.yml file to your home directory. 
Edit this file:
```yaml
site:
  available: ~/etc/nginx/sites-available
  enabled: ~/etc/nginx/sites-enabled
```

Run `init --dump-complete` will make a completion script.
Add shell complete in `~/.zshrc` or `~/.bash_profile`: `source ~/.site-cli.sh`

Usage
-----
```bash
$ site-cli [command] [argment]
```

Command:
+ **config**   Setting your .site-cli.yml
+ **disable**  Disable a site or a group sites
+ **enable**   Enable a site or a group sites
+ **help**     Displays help for a command
+ **init**     Init your site-cli configure
+ **list**     Lists sites or groups or servers
+ **vim**      Edit site configuration using Vim

Optional
---------

### Add nginx.conf vim syntax:
Download nginx.vim [nginx vim](http://www.vim.org/scripts/script.php?script_id=1886)
```bash
mv nginx.vim ~/.vim/syntax/
vi ~/.vim/filetype.vim
```
Add `au BufRead,BufNewFile your_nginx_path/* set ft=nginx`

License
-------
The Site CLI is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).