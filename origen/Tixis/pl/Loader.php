<?php

namespace tixis\pl;

use pocketmine\event\Listener;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use tixis\pl\libs\Window;
use tixis\pl\staff\StaffListener;
use tixis\pl\staff\StaffManager;
use tixis\pl\staff\StaffModeCommand;

class Loader extends PluginBase implements Listener
{

    /** @var Loader */
    protected static $instance;
    /** @var StaffManager */
    protected $staffManager;
    /** @var Config */
    protected $messagesConfig;
    /** @var Config */
    protected $itemsConfig;

    /**
     * @return Loader
     */
    public static function getInstance(): Loader
    {
        return self::$instance;
    }

    /**
     * @return StaffManager
     */
    public function getStaffManager(): StaffManager
    {
        return $this->staffManager;
    }

    /**
     * @return Config
     */
    public function getMessagesConfig(): Config
    {
        return $this->messagesConfig;
    }

    /**
     * @return Config
     */
    public function getItemsConfig(): Config
    {
        return $this->itemsConfig;
    }

    public function onEnable()
    {
        self::$instance = $this;
        
        @mkdir($this->getDataFolder());
        $this->staffManager = new StaffManager();
        $this->messagesConfig = new Config(Utilities::getFolder()."messages.yml", Config::YAML, [
            "staffmode" => [
                "on" => "§eActivaste el StaffMode",
                "off" => "§cDesactivaste el StaffMode"
            ],
            "vanish" => [
                "on" => "§eActivaste el Vanish",
                "off" => "§cDesactivaste el Vanish"
            ],
            "freeze" => [
                "staff" => [
                    "on" => "§eCongelaste a §f§l{PLAYER}",
                    "off" => "§cDescongelaste a §f§l{PLAYER}"
                ],
                "hacker" => [
                    "on" => "§cFuiste congelado por §f§l{PLAYER}",
                    "off" => "§eFuiste descongelado por §f§l{PLAYER}"
                ]
            ],
            "random-teleport" => [
                "on" => "§eFuiste teletransportado hacia §f§l{PLAYER}",
                "off" => "§cNo fue posible usar este objeto debido a: §f§lNo hay jugadores online"
            ],
            "no-permission" => "§cNo tienes permiso para usar este comando"
        ]);
        $this->itemsConfig = new Config(Utilities::getFolder()."items.yml", Config::YAML, [
            "vanish" => ItemIds::COMPASS.":0:1:§a§lVanish§r\n§7Left or Right Click to Enable/Disable Vanish",
            "freeze" => ItemIds::STICK.":0:1:§a§lFreeze§r\n§7(Pegale a alguien para congelarlo/descongelarlo)",
            "random-teleport" => ItemIds::BLAZE_ROD.":0:1:§a§lRandom TP§r\n§7Right or Left Click to teleport to Random Players",
            "inventory" => ItemIds::BOOK.":0:1:§3§lInventory§r\n§7Hit Someone to see their Inventory"
        ]);
        $this->messagesConfig->save();
        $this->itemsConfig->save();
        file_put_contents(Utilities::getFolder()."ayuda.txt", implode("\n", [
            "Permisos del plugin:",
            "  - staffmode",
            "",
            "Comandos:",
            "  - /staffmode",
            "  - /staff",
            "  - /mod",
            "",
            "Plugin Made by Tixis Especially for AkbalHCF",
            "",
            "JrDev :: Tixis",
            ".",
            ".",
            "Discord: Tixis#4066"
        ]));
        $this->getServer()->getPluginManager()->registerEvents(new StaffListener(), $this);
        $this->getServer()->getCommandMap()->register("Tixis", new StaffModeCommand($this));
        Window::registerHandler($this);
        $this->getLogger()->info("StaffMode from JrDeveloper::Tixis (#4066), VENTA PROHIBIDA DE ESTE PLUGIN");
    }

}
