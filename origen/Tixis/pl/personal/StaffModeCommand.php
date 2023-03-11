<?php

namespace Tixis\pl\staff;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use tixis\pl\Loader;
use tixis\pl\Utilities;

class StaffModeCommand extends Command
{

    /** @var Loader */
    protected $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
        parent::__construct("staffmode", "§7Activate Staff Mode", "/staffmode", ["staff", "modmode", "mod"]);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if (!$sender instanceof Player)
        {
            $sender->sendMessage("§cRun this command in-game");
            return;
        }

        $hasPermission = $sender->hasPermission("staffmode");

        if (!$hasPermission)
        {
            $sender->sendMessage(Loader::getInstance()->getMessagesConfig()->get('no-permission'));
            return;
        }

        $isInStaffMode = Loader::getInstance()->getStaffManager()->isInStaffMode($sender);
        if ($isInStaffMode)
        {
            Loader::getInstance()->getStaffManager()->removeStaffMode($sender);
            Loader::getInstance()->getStaffManager()->restore($sender);
            $sender->setAllowFlight(false);
            $message = yaml_parse_file(Utilities::getFolder()."messages.yml")['staffmode']['off'];
        } else {
            Loader::getInstance()->getStaffManager()->setStaffMode($sender);
            Loader::getInstance()->getStaffManager()->backup($sender);
            $sender->getInventory()->clearAll();
            $sender->removeAllEffects();
            $config = Loader::getInstance()->getItemsConfig();
            $vanishItem = Utilities::getItemByString($config->get('vanish'));
            $randomTeleportItem = Utilities::getItemByString($config->get('random-teleport'));
            $freezeItem = Utilities::getItemByString($config->get('freeze'));
            $inventoryItem = Utilities::getItemByString($config->get('inventory'));
            $sender->getInventory()->setItem(0, $vanishItem);
            $sender->getInventory()->setItem(2, $inventoryItem);
            $sender->getInventory()->setItem(6, $freezeItem);
            $sender->getInventory()->setItem(8, $randomTeleportItem);
            $sender->setAllowFlight(true);
            $message = yaml_parse_file(Utilities::getFolder()."messages.yml")['staffmode']['on'];
        }

        $sender->sendMessage($message);

    }
}
