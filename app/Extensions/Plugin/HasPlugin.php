<?php

namespace App\Extensions\Plugin;

trait HasPlugin
{
    protected object $plugin;

    public function setPlugin(object $plugin): void
    {
        $this->plugin = $plugin;
    }
}
