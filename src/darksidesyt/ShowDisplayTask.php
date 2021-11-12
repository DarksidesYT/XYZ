<?php
declare(strict_types=1);

namespace darksidesyt;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class ShowDisplayTask extends Task{
	private $player;
	private $mode;

	public function __construct(Player $player, string $mode = "popup"){
		$this->player = $player;
		$this->mode = $mode;
	}

	public function onRun(int $currentTick) : void{
		assert(!$this->player->isClosed());
		$location = "Coordonées: " . TextFormat::GREEN . "(" . Utils::getFormattedCoords($this->player->getX(), $this->player->getY(), $this->player->getZ()) . ")" . TextFormat::WHITE . "\n";
		$direction = "Direction: " . TextFormat::GREEN . Utils::getCompassDirection($this->player->getYaw()) . " (" . $this->player->getYaw() . ")" . TextFormat::WHITE . "\n";

		switch($this->mode){
			case "tip":
				$this->player->sendTip($location . $direction);
				break;
			case "popup":
				$this->player->sendPopup($location . $direction);
				break;
			default:
				break;
		}
	}

}
