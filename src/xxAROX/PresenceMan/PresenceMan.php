<?php
namespace xxAROX\PresenceMan;
use pocketmine\plugin\PluginBase;


/**
 * Class PresenceMan
 * @package xxAROX\PresenceMan
 * @author Jan Sohn / xxAROX
 * @date 09. Juli, 2023 - 22:30
 * @ide PhpStorm
 * @project pmmp
 */
final class PresenceMan extends PluginBase {
    public static string $NETWORK = "undefined";
    public static string $SERVER = "undefined";


    public function onLoad(): void{
        $this->saveResource("config.yml");
        $config = $this->getConfig();
        self::$NETWORK = getenv("PRESENCE_MAN_NETWORK") == false || empty(getenv("PRESENCE_MAN_NETWORK")) ? $config->get("network", self::$NETWORK) : getenv("PRESENCE_MAN_NETWORK");
        self::$SERVER = getenv("PRESENCE_MAN_SERVER") == false || empty(getenv("PRESENCE_MAN_SERVER")) ? $config->get("server", self::$SERVER) : getenv("PRESENCE_MAN_SERVER");
        $this->getLogger()->notice("This server will be displayed as " . self::$SERVER . " on " . self::$NETWORK . " in rich-presences!");
    }

    public function onEnable(): void{
        // TODO
    }
}