<?php
namespace xxAROX\PresenceMan;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use xxAROX\PresenceMan\entity\ApiActivity;
use xxAROX\PresenceMan\task\async\FetchGatewayInformationTask;


/**
 * Class PresenceMan
 * @package xxAROX\PresenceMan
 * @author Jan Sohn / xxAROX
 * @date 09. Juli, 2023 - 22:30
 * @ide PhpStorm
 * @project pmmp
 */
final class PresenceMan extends PluginBase {
	use SingletonTrait{
		reset as private;
		setInstance as private;
	}
    public static ?string $CLIENT_ID = null;
    public static string $NETWORK = "undefined";
    public static string $SERVER = "undefined";

	/** @var ApiActivity[] */
	private static array $presences = []; // TODO


    public function onLoad(): void{
		self::setInstance($this);
        $this->saveResource("config.yml");
        $config = $this->getConfig();
        self::$CLIENT_ID = getenv("PRESENCE_MAN_CLIENT_ID") == false || empty(getenv("PRESENCE_MAN_CLIENT_ID")) ? $config->get("client_id", self::$CLIENT_ID) : getenv("PRESENCE_MAN_CLIENT_ID");
        self::$NETWORK = getenv("PRESENCE_MAN_NETWORK") == false || empty(getenv("PRESENCE_MAN_NETWORK")) ? $config->get("network", self::$NETWORK) : getenv("PRESENCE_MAN_NETWORK");
        self::$SERVER = getenv("PRESENCE_MAN_SERVER") == false || empty(getenv("PRESENCE_MAN_SERVER")) ? $config->get("server", self::$SERVER) : getenv("PRESENCE_MAN_SERVER");
    }

    public function onEnable(): void{
		// NOTE: fetch backend ip
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getAsyncPool()->submitTask(new FetchGatewayInformationTask());
    }
}