<?php

use pocketmine\plugin\PluginBase;


final class PresenceMan extends PluginBase {
    public static string $NETWORK = "undefined";
    public static string $SERVER_NAME = "undefined";


    public function onLoad(): void{
        $this->saveResources("config.yml");
        $config = $this->getConfig();
        self::$NETWORK = getenv("PRESENCE_MAN_NETWORK") === false || empty(getenv("PRESENCE_MAN_NETWORK")) ? $config->get("network", self::$NETWORK) : getenv("PRESENCE_MAN_NETWORK");
        self::$SERVER_NAME = getenv("PRESENCE_MAN_SERVER_NAME") === false || empty(getenv("PRESENCE_MAN_SERVER_NAME")) ? $config->get("server-name", self::$SERVER_NAME) : getenv("PRESENCE_MAN_SERVER_NAME");
        $this->getLogger()->notice("This server is " . self::$SERVER_NAME . " on " . self::$NETWORK . "!");
    }

    public function onEnable(): void{
        // TODO
    }
}