<?php

namespace tixis\pl\staff;

use pocketmine\entity\Effect;
use pocketmine\level\Position;
use pocketmine\Player;
use tixis\pl\Utilities;

class StaffManager
{

    protected $freeze = [];
    protected $vanish = [];
    protected $staff = [];
    protected $backup = [];

    public function backup(Player $player)
    {
        if (isset($this->backup[Utilities::getLowerName($player)]))
            unset($this->backup[Utilities::getLowerName($player)]);
        $this->backup[Utilities::getLowerName($player)] = [
            $player->getInventory()->getContents(),
            $player->getInventory()->getArmorContents(),
            $player->getEffects(),
            Position::fromObject($player, $player->getLevel()),
            $player->getGamemode()
        ];
    }

    public function restore(Player $player)
    {
        if (!$this->backup[Utilities::getLowerName($player)]) return;

        $arr = $this->backup[Utilities::getLowerName($player)];
        $player->setGamemode($arr[4]);

        $player->getInventory()->setContents($arr[0]);
        $player->getInventory()->setArmorContents($arr[1]);
        /** @var Effect $effect */
        foreach ($arr[2] as $effect)
        {
            $player->addEffect($effect);
        }

        $player->teleport($arr[3]);

        unset($this->backup[Utilities::getLowerName($player)]);

    }

    public function isFreezed(Player $player): bool
    {
        return in_array(Utilities::getLowerName($player), $this->getFreeze());
    }

    public function isVanished(Player $player): bool
    {
        return in_array(Utilities::getLowerName($player), $this->getVanish());
    }

    public function isInStaffMode(Player $player): bool
    {
        return in_array(Utilities::getLowerName($player), $this->getStaff());
    }

    public function setFreezed(Player $player)
    {
        if ($this->isFreezed($player)) return;

        $this->freeze[] = Utilities::getLowerName($player);
    }

    public function setVanished(Player $player)
    {
        if ($this->isVanished($player)) return;

        $this->vanish[] = Utilities::getLowerName($player);
    }

    public function setStaffMode(Player $player)
    {
        if ($this->isInStaffMode($player)) return;

        $this->staff[] = Utilities::getLowerName($player);
    }

    public function removeFreeze(Player $player)
    {
        if (!$this->isFreezed($player)) return;

        $index = array_search(Utilities::getLowerName($player), $this->getFreeze());
        unset($this->freeze[$index]);
    }

    public function removeVanish(Player $player)
    {
        if (!$this->isVanished($player)) return;

        $index = array_search(Utilities::getLowerName($player), $this->getVanish());
        unset($this->vanish[$index]);
    }

    public function removeStaffMode(Player $player)
    {
        if (!$this->isInStaffMode($player)) return;

        $index = array_search(Utilities::getLowerName($player), $this->getStaff());
        unset($this->staff[$index]);
    }

    /**
     * @return array
     */
    public function getFreeze(): array
    {
        return $this->freeze;
    }

    /**
     * @return array
     */
    public function getStaff(): array
    {
        return $this->staff;
    }

    /**
     * @return array
     */
    public function getVanish(): array
    {
        return $this->vanish;
    }

}