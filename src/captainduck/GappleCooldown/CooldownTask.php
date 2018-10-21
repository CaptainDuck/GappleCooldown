<?php

namespace captainduck\GappleCooldown;

use pocketmine\scheduler\Task;

class CooldownTask extends Task{

    private $plugin;

    public function __construct(GappleCooldown $plugin)
    {
        $this->plugin = $plugin;
    }


    public function onRun($tick){
        $this->plugin->timer();
    }
}
