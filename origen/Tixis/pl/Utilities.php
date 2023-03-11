<?php

namespace tixis\pl;

use pocketmine\item\Item;
use pocketmine\Player;

class Utilities
{

    public static function getLowerName(Player $player): string
    {
        return strtolower($player->getName());
    }

    public static function getFolder(): string
    {
        return Loader::getInstance()->getDataFolder();
    }

    public static function getItemByString(string $itemString): Item
    {
        $arr = explode(":", $itemString);

        $id = (int) $arr[0];
        $meta = (int) $arr[1];
        $count = (int) $arr[2];

        $complicateString = yaml_emit("§");

        $customName = str_replace(["&", $complicateString], ["§", "§"], $arr[3]);

        return Item::get($id, $meta, $count)->setCustomName($customName);
    }

}