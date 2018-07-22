<?php

namespace captainduck\GappleCooldown;

use pocketmine\plugin\PluginBase;
use pocketmine\{
    Player, Server
};
use pocketmine\utils\{
    Config, TextFormat as C
};
use captainduck\GappleCooldown\Task;
use pocketmine\event\player\PlayerItemConsumeEvent;

class Main extends PluginBase implements \pocketmine\event\Listener{

    private $p = [];

    public function onEnable(){
        $this->getLogger()->info("GappleCooldown by CaptainDuck enabled!");
        $this->getScheduler()->scheduleRepeatingTask(new Task($this, 25), 25);
        $this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML, array(
            "cooldown-seconds" => 10,
            "enchanted-cooldown-seconds" => 20
        ));
        $this->cooldown = new Config($this->getDataFolder(). "cooldowns.yml", Config::YAML);
        if(!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable(){
        $this->config->save();
        $this->cooldown->save();
    }

    public function onConsume(PlayerItemConsumeEvent $e){
        $player = $e->getPlayer();
        if($e->getItem()->getId() == 322){
          if(isset($this->p[strtolower($player->getName())])){
            $player->sendMessage(C::ITALIC. C::RED. "You're able to consume another Golden Apple in ". $this->cooldown->get(strtolower($player->getName())). " seconds.");
            $e->setCancelled();
          }else{
            $this->addCooldown($player);
          }
        }
        if($e->getItem()->getId() == 466){
          if(isset($this->p[strtolower($player->getName())])){
            $player->sendMessage(C::ITALIC. C::RED. "You're able to consume another Golden Apple in ". $this->cooldown->get(strtolower($player->getName())). " seconds.");
            $e->setCancelled();
          }else{
            $this->addEnchantedCooldown($player);
          }
        }
      }

    public function timer(){
        foreach($this->cooldown->getAll() as $player => $time){
		    $time--;
		    $this->cooldown->set($player, $time);
		    $this->cooldown->save();
		    if($time == 0){
		        $this->cooldown->remove($player);
                unset($this->p[$player]);
			    $this->cooldown->save();
            }
        }
    }


    public function addCooldown($player){
        $this->cooldown->set(strtolower($player->getName()), $this->config->get("cooldown-seconds"));
        $this->p[strtolower($player->getName())] = strtolower($player->getName());
        $this->cooldown->save();
    }

    public function addEnchantedCooldown($player){
        $this->cooldown->set(strtolower($player->getName()), $this->config->get("enchanted-cooldown-seconds"));
        $this->p[strtolower($player->getName())] = strtolower($player->getName());
        $this->cooldown->save();
    }
}
