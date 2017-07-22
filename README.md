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

Site CLI makes me lazy in the development, It's more than just `cd` or `ln -s` , I enjoy it.

Features
--------
+ Command completion
+ Site grouping
+ List site information
+ Quick switching group/site/server
+ Quick build configuration file from template

Installation
-------------

Download Phar file: 

+ [Phar Releases]((https://github.com/panlatent/site-cli/releases))
```bash
$ mv site-cli.phar /usr/local/bin/site
$ chmod +x /usr/local/bin/site
```

Download the library using composer:
```bash
$ composer require panlatent/site-cli
```

```bash
$ ln -s ./bin/site-cli /usr/local/bin/site
$ chmod +x /usr/local/bin/site
```

Configuration
-------------
### Custom Configuration

The default configuration provided by Site Cli works very well, but you can still customize it.

Run `init` command will create a .site-cli.yml file to your home directory.
Edit this file:
```yaml
site:
  available: ~/etc/nginx/sites-available
  enabled: ~/etc/nginx/sites-enabled
```

### Add Completion

Run `init --dump-completion` will make a completion script contents and print to the terminal.
Use `-o, --output[=OUTPUT]` will write to a file. 
Add shell complete in `~/.zshrc` or `~/.bash_profile`: `source ~/.site-cli.sh`

A example: 
```bash
$ site init --dump-comletion -o ~/.site-cli.bash
$ echo "source .site-cli.bash" >> ~/.zshrc
```

Usage
-----
```bash
$ site [command] [argment]
```

Command:
+ **config**   Get and set site-cli options
+ **ctl**      Control site service process
+ **disable**  Disable a site or a group sites
+ **edit**     Edit site configuration using editor
+ **enable**   Enable a site or a group sites
+ **help**     Displays help for a command
+ **init**     Init site-cli settings
+ **ls**       List groups and sites contents

Optional
---------

### Add nginx.conf vim syntax:
Download nginx.vim [nginx vim](http://www.vim.org/scripts/script.php?script_id=1886)
```bash
$ mv nginx.vim ~/.vim/syntax/
$ vi ~/.vim/filetype.vim
```
Add `au BufRead,BufNewFile your_nginx_path/* set ft=nginx`

License
-------
The Site CLI is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).