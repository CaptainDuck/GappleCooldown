<?php

namespace captainduck\GappleCooldown;

use captainduck\GappleCooldown\Main;
use pocketmine\scheduler\PluginTask;

class Task extends PluginTask{

    private $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }


    public function onRun($tick){
        $this->plugin->timer();
    }
}
