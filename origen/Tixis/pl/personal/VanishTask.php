<?php

namespace tixis\pl\staff;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use tixis\pl\Loader;

class VanishTask extends Task
{

    /** @var Player */
    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function onRun($currentTick)
    {
        $player = $this->player;

        if (!$player->isOnline())
        {
            Loader::getInstance()->getServer()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }

        $players = Loader::getInstance()->getServer()->getOnlinePlayers();
        foreach ($players as $p){
            if (Loader::getInstance()->getStaffManager()->isVanished($p)) $player->hidePlayer($p); else $player->showPlayer($p);
        }

    }
}