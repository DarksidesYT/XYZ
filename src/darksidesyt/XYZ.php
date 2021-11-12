<?php
declare(strict_types=1);

namespace darksidesyt;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;

class XYZ extends PluginBase implements Listener{
	private $tasks = [];
	private $refreshRate = 1;
	private $mode = "popup";

	public function onEnable() : void{
		if(!is_dir($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		if(!file_exists($this->getDataFolder() . "config.yml")){
			$this->saveDefaultConfig();
		}

		$this->refreshRate = (int) $this->getConfig()->get("refreshRate");
		if($this->refreshRate < 1){
			$this->getLogger()->warning("Refresh rate est en dessous de 1 faites attention");
			$this->getConfig()->set("refreshRate", 1);
			$this->getConfig()->save();
			$this->refreshRate = 1;
		}

		$this->mode = $this->getConfig()->get("displayMode", "popup");
		if($this->mode !== "tip" and $this->mode !== "popup"){
			$this->getLogger()->warning("Dipslay mode Invalide " . $this->mode . ", remise a `popup`");
			$this->getConfig()->set("displayMode", "popup");
			$this->getConfig()->save();
			$this->mode = "popup";
		}

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable() : void{
		$this->tasks = [];
	}

	public function onCommand(CommandSender $sender, Command $command, string $aliasUsed, array $args) : bool{
		if($command->getName() === "xyz"){
			if(!($sender instanceof Player)){
				$sender->sendMessage(TextFormat::RED . "Cette commande n'est pas utilisable dans la console");

				return true;
			}
			if(!$sender->hasPermission("use.xyz")){
				$sender->sendMessage(TextFormat::RED . $this->getConfig()->get("permissionError"));

				return true;
			}

			if(!isset($this->tasks[$sender->getName()])){
				$this->tasks[$sender->getName()] = $this->getScheduler()->scheduleRepeatingTask(new ShowDisplayTask($sender, $this->mode), $this->refreshRate);
				$sender->sendMessage(TextFormat::GREEN . "Commande Activer!");
			}else{
				$this->stopDisplay($sender->getName());
				$sender->sendMessage(TextFormat::GREEN . "Commande désactivé !");
			}

			return true;
		}

		return false;
	}

	private function stopDisplay(string $playerFor) : void{
		if(isset($this->tasks[$playerFor])){
			$this->getScheduler()->cancelTask($this->tasks[$playerFor]->getTaskId());
			unset($this->tasks[$playerFor]);
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$this->stopDisplay($event->getPlayer()->getName());
	}
}
