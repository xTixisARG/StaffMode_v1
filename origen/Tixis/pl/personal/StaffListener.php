<?php

namespace tixis\pl\staff;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use tixis\pl\libs\Window;
use tixis\pl\Loader;
use tixis\pl\Utilities;

class StaffListener implements Listener
{

    public function cancelingMovingCauseFreeze(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();

        if (Loader::getInstance()->getStaffManager()->isFreezed($player))
            $event->setCancelled(true);
    }

    public function cancelingHitsCauseStaffModeOrFreeze(EntityDamageEvent $event)
    {
        $victim = $event->getEntity();

        if (!$event instanceof EntityDamageByEntityEvent) return;

        $damager = $event->getDamager();

        if (!$victim instanceof Player || !$damager instanceof Player) return;

        if (Loader::getInstance()->getStaffManager()->isInStaffMode($victim) || Loader::getInstance()->getStaffManager()->isInStaffMode($damager))
            $event->setCancelled(true);
        if (Loader::getInstance()->getStaffManager()->isFreezed($victim) || Loader::getInstance()->getStaffManager()->isFreezed($damager))
            $event->setCancelled(true);
    }

    public function gettingFreezedAndAll(EntityDamageEvent $event)
    {
        $victim = $event->getEntity();

        if (!$event instanceof EntityDamageByEntityEvent) return;

        $damager = $event->getDamager();

        if (!$victim instanceof Player || !$damager instanceof Player) return;

        $config = Loader::getInstance()->getItemsConfig();
        $itemT = Utilities::getItemByString($config->get('freeze'));
        $inventoryItem = Utilities::getItemByString($config->get('inventory'));

        $item = $damager->getInventory()->getItemInHand();

        $customName = $item->getCustomName();

        if ($customName == $itemT->getCustomName()){
            $freezed = Loader::getInstance()->getStaffManager()->isFreezed($victim);
            $messageArrS = yaml_parse_file(Utilities::getFolder()."messages.yml")['freeze']['staff'];
            $messageArrH = yaml_parse_file(Utilities::getFolder()."messages.yml")['freeze']['hacker'];
            if ($freezed){
                $messageStaff = str_replace("{PLAYER}", $victim->getName(), $messageArrS['off']);
                $messageHacker = str_replace("{PLAYER}", $damager->getName(), $messageArrH['off']);
                Loader::getInstance()->getStaffManager()->removeFreeze($victim);
            } else {
                $messageStaff = str_replace("{PLAYER}", $victim->getName(), $messageArrS['on']);
                $messageHacker = str_replace("{PLAYER}", $damager->getName(), $messageArrH['on']);
                Loader::getInstance()->getStaffManager()->setFreezed($victim);
            }
            $damager->sendMessage($messageStaff);
            $victim->sendMessage($messageHacker);
        }
        if ($customName == $inventoryItem->getCustomName()){
            $callable = function (Window $window, Player $player, Item $item, InventoryTransactionEvent $e) {
                $e->setCancelled(true);
            };
            $window = new Window($damager->getPosition(), "§7Inventario de §l{$victim->getName()}", Window::DOUBLE_CHEST, $callable);

            $window->setContents($victim->getInventory()->getContents());

            $damager->addWindow($window);
        }

    }

    public function joiningToTheServer(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();

        Loader::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new VanishTask($player), 20);
    }

    public function quittingServer(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();

        Loader::getInstance()->getStaffManager()->restore($player);
    }

    public function interactingWithItems(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();

        $item = $player->getInventory()->getItemInHand();

        $customName = $item->getCustomName();

        $config = Loader::getInstance()->getItemsConfig();
        $vanishItem = Utilities::getItemByString($config->get('vanish'));
        $randomTeleportItem = Utilities::getItemByString($config->get('random-teleport'));

        if ($customName == $vanishItem->getCustomName())
        {
            $vanished = Loader::getInstance()->getStaffManager()->isVanished($player);
            $messageArr = yaml_parse_file(Utilities::getFolder()."messages.yml")['vanish'];
            if ($vanished)
            {
                $message = $messageArr['off'];
                Loader::getInstance()->getStaffManager()->removeVanish($player);
            } else {
                $message = $messageArr['on'];
                Loader::getInstance()->getStaffManager()->setVanished($player);
            }
            $player->sendMessage($message);
            return;
        }

        if ($customName == $randomTeleportItem->getCustomName())
        {
            $players = Loader::getInstance()->getServer()->getOnlinePlayers();
            $messageArr = yaml_parse_file(Utilities::getFolder()."messages.yml")['random-teleport'];
            if (count($players) < 2){
                $player->sendMessage($messageArr['off']);
                return;
            }

            /** @var Player[] $randPlayerArr */
            $randPlayerArr = [];

            foreach ($players as $p){
                if (Utilities::getLowerName($p) == Utilities::getLowerName($player)) continue;

                $randPlayerArr[] = $p;

            }

            $randPlayer = $randPlayerArr[array_rand($randPlayerArr)];
            $message = str_replace("{PLAYER}", $randPlayer->getName(), $messageArr['on']);
            $player->teleport($randPlayer);
            $player->sendMessage($message);

        }

    }

}