<?php
namespace xxAROX\PresenceMan;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use xxAROX\PresenceMan\entity\ActivityType;
use xxAROX\PresenceMan\entity\ApiActivity;
use xxAROX\PresenceMan\entity\ApiRequest;
use xxAROX\PresenceMan\entity\Gateway;
use xxAROX\PresenceMan\task\async\BackendRequest;
use xxAROX\PresenceMan\task\async\FetchGatewayInformationTask;
use xxAROX\PresenceMan\utils\SkinUtils;


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
	private static string $TOKEN = "undefined";
	public static ?string $CLIENT_ID = null;
    public static string $SERVER = "undefined";
	public static bool $ENABLE_DEFAULT = false;

	/**
	 * @var ApiActivity[]
	 * @readonly
	 * @final
	 */
	public static array $presences = [];
	public static ApiActivity $default;

	protected function onLoad(): void{
		self::setInstance($this);
        $this->saveResource("config.yml");
        $config = $this->getConfig();
		self::$TOKEN = getenv("PRESENCE_MAN_TOKEN") == false || empty(getenv("PRESENCE_MAN_TOKEN")) ? $config->get("token", self::$TOKEN) : getenv("PRESENCE_MAN_TOKEN");
		self::$CLIENT_ID = getenv("PRESENCE_MAN_CLIENT_ID") == false || empty(getenv("PRESENCE_MAN_CLIENT_ID")) ? $config->get("client_id", self::$CLIENT_ID) : getenv("PRESENCE_MAN_CLIENT_ID");
		self::$SERVER = getenv("PRESENCE_MAN_SERVER") == false || empty(getenv("PRESENCE_MAN_SERVER")) ? $config->get("server", self::$SERVER) : getenv("PRESENCE_MAN_SERVER");
		self::$ENABLE_DEFAULT = getenv("PRESENCE_MAN_DEFAULT_ENABLED") == false || empty(getenv("PRESENCE_MAN_DEFAULT_ENABLED")) ? $config->get("enable_default", self::$ENABLE_DEFAULT) : getenv("PRESENCE_MAN_DEFAULT_ENABLED");

		$DEFAULT_STATE = getenv("PRESENCE_MAN_DEFAULT_STATE") == false || empty(getenv("PRESENCE_MAN_DEFAULT_STATE")) ? $config->get("default_state", "null") : getenv("PRESENCE_MAN_DEFAULT_STATE");
		$DEFAULT_DETAILS = getenv("PRESENCE_MAN_DEFAULT_DETAILS") == false || empty(getenv("PRESENCE_MAN_DEFAULT_DETAILS")) ? $config->get("default_details", "null") : getenv("PRESENCE_MAN_DEFAULT_DETAILS");
		$DEFAULT_LARGE_IMAGE_KEY = getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_KEY") == false || empty(getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_KEY")) ? $config->get("default_large_image_key", "bedrock") : getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_KEY");
        $DEFAULT_LARGE_IMAGE_TEXT = getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_TEXT") == false || empty(getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_TEXT")) ? $config->get("default_large_image_text", "Minecraft: Bedrock Edition") : getenv("PRESENCE_MAN_DEFAULT_LARGE_IMAGE_TEXT");
		self::$default = new ApiActivity(
			ActivityType::PLAYING(),
			$DEFAULT_STATE,
			$DEFAULT_DETAILS,
			null,
			$DEFAULT_LARGE_IMAGE_KEY,
			$DEFAULT_LARGE_IMAGE_TEXT
		);
    }
    protected function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getAsyncPool()->submitTask(new FetchGatewayInformationTask());
    }
	protected function onDisable(): void{
		foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) self::offline($onlinePlayer);
		parent::onDisable();
	}

	/**
	 * Function setActivity
	 * @param Player $player
	 * @param null|ApiActivity $activity
	 * @return void
	 */
	public static function setActivity(Player $player, ?ApiActivity $activity = null): void{
		if (!Server::getInstance()->isRunning()) return;
		if (!$player->isConnected()) return;
		if (empty($player->getXuid())) return;

		$request = new ApiRequest(ApiRequest::$URI_UPDATE_PRESENCE, [
			"ip" => $player->getNetworkSession()->getIp(),
			"xuid" => $player->getXuid(),
			"server" => PresenceMan::$SERVER,
			"api_activity" => $activity?->json_serialize(),
		], true);
		$request->header("Token", self::$TOKEN);
		Server::getInstance()->getAsyncPool()->submitTask(new BackendRequest(
			$request->serialize(),
			function (array $response) use ($player, $activity): void{
				if (isset($response["status"]) == 200) self::$presences[$player->getXuid()] = $activity;
				else PresenceMan::getInstance()->getLogger()->error("Failed to update presence for " . $player->getName() . ": " . $response["message"] ?? "n/a");
			}
		));
	}

	/**
	 * Function getHeadUrl
	 * @param string $xuid
	 * @return string
	 */
	public static function getHeadUrl(string $xuid): string{
		return Gateway::getUrl() . "/api/v1/heads/$xuid";
	}

	/**
	 * Function offline
	 * @param Player $player
	 * @return void
	 * @internal
	 */
	public static function offline(Player $player): void{
		$request = new ApiRequest(ApiRequest::$URI_OFFLINE, [
			"ip" => $player->getNetworkSession()->getIp(),
			"xuid" => $player->getXuid()
		], true);
		$request->header("Token", self::$TOKEN);
		$task = new BackendRequest(
			$request->serialize(),
			function (array $response) use ($player): void{
				unset(self::$presences[$player->getXuid()]);
			}
		);

		if (!Server::getInstance()->isRunning()) {
			try {
				$task->run();
			} catch (\Throwable $ignore) {
			}
		} else Server::getInstance()->getAsyncPool()->submitTask($task);
	}

	/**
	 * Function save_head
	 * @param Player $player
	 * @param Skin $skin
	 * @return void
	 * @internal
	 */
	public static function save_head(Player $player, Skin $skin): void{
		if (!Server::getInstance()->isRunning()) return;
		if (empty($player->getXuid())) return;

		$head = SkinUtils::getHead($player, $skin);
		if (empty($head)) return;

		$request = new ApiRequest(ApiRequest::$URI_UPDATE_HEAD, [
			"ip" => $player->getNetworkSession()->getIp(),
			"xuid" => $player->getXuid(),
			"head" => $head,
		], true);
		$request->header("Token", self::$TOKEN);
		$task = new BackendRequest($request->serialize());

		if (!Server::getInstance()->isRunning()) {
			try {
				$task->run();
			} catch (\Throwable $ignore) {
			}
		} else Server::getInstance()->getAsyncPool()->submitTask($task);
	}

	/**
	 * Function applyToken
	 * @param ApiRequest $request
	 * @return void
	 * @internal
	 */
	public function applyToken(ApiRequest $request): void{
		$request->header("Token", self::$TOKEN);
	}
}
