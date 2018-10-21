<?php

namespace captainduck\GappleCooldown;

use pocketmine\plugin\PluginBase;
use pocketmine\{
    Player, Server
};
use pocketmine\utils\Config;
use captainduck\GappleCooldown\CooldownTask;
use pocketmine\event\player\PlayerItemConsumeEvent;

class GappleCooldown extends PluginBase implements \pocketmine\event\Listener{

    public function onEnable(){
        $this->getLogger()->info("GappleCooldown by CaptainDuck enabled!");
        $this->getScheduler()->scheduleRepeatingTask(new CooldownTask($this, 25), 25);
        $this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML, array(
            "cooldown-seconds" => 10,
            "enchanted-cooldown-seconds" => 20,
            "#Available variables: {TIME} - returns the cooldown time in h:i:s format, {NAME} - returns the player name.",
            "has-cooldown-message" => "You will be able to consume another golden apple in {TIME} minutes!"
        ));
        $this->cooldown = new Config($this->getDataFolder(). "cooldowns.yml", Config::YAML);
        @mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable(){
        $this->config->save();
        $this->cooldown->save();
    }

    public function convertSeconds(int $seconds) : string {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        return "$hours:$minutes:$seconds";
    }

    public function formatMessage(string $message, $player) : string {
        $message = str_replace("{TIME}", $this->getCooldownTime($player), $message);
        $message = str_replace("{NAME}", $player->getName(), $message);
        return $message;
    }

    public function onConsume(PlayerItemConsumeEvent $e){
        $player = $e->getPlayer();
        if($e->getItem()->getId() == 322){
          if($this->hasCooldown($player)){
            $player->sendMessage($this->formatMessage($this->config->get("has-cooldown-message"), $player));
            $e->setCancelled();
          }else{
            $this->addCooldown($player);
          }
        }
        if($e->getItem()->getId() == 466){
          if($this->hasCooldown($player)){
            $player->sendMessage($this->formatMessage($this->config->get("has-cooldown-message"), $player));
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
			    $this->cooldown->save();
            }
        }
    }

    public function hasCooldown($player){
        return $this->cooldown->exists($player->getLowerCaseName());
    }

    public function getCooldownSeconds($player){
        return $this->cooldown->get($player->getLowerCaseName());
    }

    public function getCooldownTime($player){
        return $this->convertSeconds($this->getCooldownSeconds($player));
    }

    public function addCooldown($player){
        $this->cooldown->set($player->getLowerCaseName(), $this->config->get("cooldown-seconds"));
        $this->cooldown->save();
    }

    public function addEnchantedCooldown($player){
        $this->cooldown->set($player->getLowerCaseName(), $this->config->get("enchanted-cooldown-seconds"));
        $this->cooldown->save();
    }
}
