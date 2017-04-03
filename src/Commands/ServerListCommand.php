<?php

namespace Panlatent\SiteCli\Commands;

class ServerListCommand extends Command
{
    protected function configure()
    {
        $this->setName('server:list');
    }
}