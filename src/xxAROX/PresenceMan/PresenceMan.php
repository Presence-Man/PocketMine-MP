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
use xxAROX\PresenceMan\utils\Utils;


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
	public static bool $UPDATE_SKIN = false;

	/**
	 * @var ApiActivity[]
	 * @readonly
	 * @final
	 */
	public static array $presences = [];
	public static ApiActivity $default;

	protected function onLoad(): void{
		self::setInstance($this);
        $this->saveResource("README.md");
        $this->saveResource("config.yml");

        $config = $this->getConfig();
		self::$TOKEN = (string) Utils::getconfigvalue($config, "token");
		self::$CLIENT_ID = (string) Utils::getconfigvalue($config, "client_id", "", self::$CLIENT_ID);
		self::$SERVER = (string) Utils::getconfigvalue($config, "server", "", self::$SERVER);
		self::$UPDATE_SKIN = (string) Utils::getconfigvalue($config, "update_skin", "", self::$UPDATE_SKIN);

		self::$ENABLE_DEFAULT = (boolean) Utils::getconfigvalue($config, "default_presence.enabled", "DEFAULT_ENABLED", self::$ENABLE_DEFAULT);
		$DEFAULT_STATE = (string) Utils::getconfigvalue($config, "default_presence.state", "DEFAULT_STATE", "Playing {server} on {network}");
		$DEFAULT_DETAILS = (string) Utils::getconfigvalue($config, "default_presence.details", "DEFAULT_DETAILS", "");
		$DEFAULT_LARGE_IMAGE_KEY = (string) Utils::getconfigvalue($config, "default_presence.large_image_key", "DEFAULT_LARGE_IMAGE_KEY", "");
		$DEFAULT_LARGE_IMAGE_TEXT = (string) Utils::getconfigvalue($config, "default_presence.large_image_text", "DEFAULT_LARGE_IMAGE_TEXT", "{App.name} - v{App.version}");

		self::$default = new ApiActivity(
			ActivityType::PLAYING,
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
}
