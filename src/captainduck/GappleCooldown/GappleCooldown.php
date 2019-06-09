<?php

namespace captainduck\GappleCooldown;

use pocketmine\plugin\PluginBase;
use pocketmine\{
    Player, Server
};
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerItemConsumeEvent;

class GappleCooldown extends PluginBase implements \pocketmine\event\Listener{

    public static $instance = null;

    public function onEnable(){
        self::$instance = $this;
        $this->getLogger()->info("GappleCooldown by CaptainDuck enabled!");
        $this->cooldown = new Config($this->getDataFolder(). "cooldowns.yml", Config::YAML);
        $this->ecooldown = new Config($this->getDataFolder(). "enchantcooldowns.yml", Config::YAML); // for egapple cooldowns
        @mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public function convertSeconds($time) {
        if($time >= 60) {
            $mins = $time / 60;
            $minutes = floor($mins);
            $secs = $mins - $minutes;
            $seconds = floor($secs * 60);

            if($minutes >= 60) {
                $hrs = $minutes / 60;
                $hours = floor($hrs);
                $mins = $hrs - $hours;
                $minutes = floor($mins * 60);
                return $hours . "h " . $minutes . "m " . $seconds . "s";
            } else {
                return $minutes . "m " . $seconds . "s";
            }
        } else {
            return $time . "s";
        }
    }

    public function formatMessage(string $message, $player, $enchanted = false) : string {
        $time = $enchanted ? $this->getCooldownTime($player) : $this->getEnchantedCooldownTime($player);
        $time = $this->convertSeconds($time);
        $message = str_replace("{TIME}", $time, $message);
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
            if($this->hasEnchantedCooldown($player)){
                $player->sendMessage($this->formatEnchantedMessage($this->config->get("has-cooldown-message"), $player));
                $e->setCancelled();
            }else{
                $this->addEnchantedCooldown($player);
            }
        }
    }

    public function hasCooldown($player) : bool {
        if($this->cooldown->exists($player->getLowerCaseName())){
            if(microtime(true) >= $this->cooldown->get($player->getLowerCaseName())){
                $this->removeCooldown($player);
                return false;
            }else{
                return false;
            }
        }else{
            return false; // doesn't have a cooldown set
        }
    }

    public function hasEnchantedCooldown($player) : bool {
        if($this->ecooldown->exists($player->getLowerCaseName())){
            if(microtime(true) >= $this->ecooldown->get($player->getLowerCaseName())){
                $this->removeEnchantedCooldown($player);
                return false;
            }else{
                return true;
            }
        }else{
            return false; // doesn't have a cooldown set
        }
    }

    public function removeCooldown(Player $player){
        $this->cooldown->remove($player->getLowerCaseName());
        $this->cooldown->save();
    }

    public function removeEnchantedCooldown(Player $player){
        $this->ecooldown->remove($player->getLowerCaseName());
        $this->ecooldown->save();
    }

    public function getCooldownSeconds(Player $player){
        return $this->cooldown->get($player->getLowerCaseName()) - microtime(true);
    }

    public function getCooldownTime($player){
        return $this->convertSeconds($this->getCooldownSeconds($player));
    }

    public function getEnchantedCooldownSeconds($player){
        return $this->ecooldown->get($player->getLowerCaseName()) - microtime(true);
    }

    public function getEnchantedCooldownTime($player){
        return $this->convertSeconds($this->getEnchantedCooldownSeconds($player));
    }

    public function addCooldown($player){
        $this->cooldown->set($player->getLowerCaseName(), microtime(true) + $this->getConfig()->get("cooldown-seconds"));
        $this->cooldown->save();
    }

    public function addEnchantedCooldown($player){
        $this->ecooldown->set($player->getLowerCaseName(), microtime(true) + $this->getConfig()->get("enchanted-cooldown-seconds"));
        $this->ecooldown->save();
    }
}
