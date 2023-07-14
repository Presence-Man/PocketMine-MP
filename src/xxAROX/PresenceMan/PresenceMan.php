<?php
namespace xxAROX\PresenceMan;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use xxAROX\PresenceMan\entity\ApiActivity;
use xxAROX\PresenceMan\entity\ApiRequest;
use xxAROX\PresenceMan\task\async\BackendRequest;
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
    public static ?string $DEFAULT_LARGE_IMAGE_KEY = null;
    public static ?string $DEFAULT_LARGE_IMAGE_TEXT = null;
    public static ?string $DEFAULT_SMALL_IMAGE_KEY = null;
    public static ?string $DEFAULT_SMALL_IMAGE_TEXT = null;

	/** @var ApiActivity[] */
	public static array $presences = []; // TODO


    public function onLoad(): void{
		self::setInstance($this);
        $this->saveResource("config.yml");
        $config = $this->getConfig();
        self::$CLIENT_ID = getenv("PRESENCE_MAN_CLIENT_ID") == false || empty(getenv("PRESENCE_MAN_CLIENT_ID")) ? $config->get("client_id", self::$CLIENT_ID) : getenv("PRESENCE_MAN_CLIENT_ID");
        self::$NETWORK = getenv("PRESENCE_MAN_NETWORK") == false || empty(getenv("PRESENCE_MAN_NETWORK")) ? $config->get("network", self::$NETWORK) : getenv("PRESENCE_MAN_NETWORK");
        self::$SERVER = getenv("PRESENCE_MAN_SERVER") == false || empty(getenv("PRESENCE_MAN_SERVER")) ? $config->get("server", self::$SERVER) : getenv("PRESENCE_MAN_SERVER");
        self::$DEFAULT_LARGE_IMAGE_KEY = getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_KEY") == false || empty(getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_KEY")) ? $config->get("default_large_image_key", self::$DEFAULT_LARGE_IMAGE_KEY) : getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_KEY");
        self::$DEFAULT_LARGE_IMAGE_TEXT = getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_TEXT") == false || empty(getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_TEXT")) ? $config->get("default_large_image_text", self::$DEFAULT_LARGE_IMAGE_TEXT) : getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_TEXT");
        self::$DEFAULT_SMALL_IMAGE_KEY = getenv("PRESENCE_MAN_DEFAULT_SMALL_IMAGE_KEY") == false || empty(getenv("PRESENCE_MAN_DEFAULT_SMALL_IMAGE_KEY")) ? $config->get("default_small_image_key", self::$DEFAULT_SMALL_IMAGE_KEY) : getenv("PRESENCE_MAN_DEFAULT_SMALL_IMAGE_KEY");
        self::$DEFAULT_SMALL_IMAGE_TEXT = getenv("PRESENCE_MAN_DEFAULT_SMALL_IMAGE_TEXT") == false || empty(getenv("PRESENCE_MAN_DEFAULT_SMALL_IMAGE_TEXT")) ? $config->get("default_small_image_text", self::$DEFAULT_SMALL_IMAGE_TEXT) : getenv("PRESENCE_MAN_DEFAULT_SMALL_IMAGE_TEXT");
    }

    public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getAsyncPool()->submitTask(new FetchGatewayInformationTask());
    }

	public static function setActivity(Player $player, ApiActivity $activity): void{
		$ip = $player->getNetworkSession()->getIp();
		$request = new ApiRequest(ApiRequest::$URI_UPDATE_PRESENCE, [
			"ip" => $ip,
			"xuid" => $player->getXuid(),
			"api_activity" => $activity,
		]);
		Server::getInstance()->getAsyncPool()->submitTask(new BackendRequest(
			$request,
			function (array $response) use ($player, $activity): void{
				if (isset($response["status"]) == 200) self::$presences[$player->getXuid()] = $activity;
				else PresenceMan::getInstance()->getLogger()->error("Failed to update presence for " . $player->getName() . ": " . $response["message"] ?? "n/a");
			},
			function (): void{
			}
		));
	}
}