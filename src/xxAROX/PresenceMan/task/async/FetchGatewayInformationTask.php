<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\task\async;
use Closure;
use GlobalLogger;
use JsonException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use xxAROX\PresenceMan\entity\Gateway;
use xxAROX\PresenceMan\PresenceMan;
use xxAROX\PresenceMan\task\ReconnectingTask;


/**
 * Class FetchGatewayInformationTask
 * @package xxAROX\PresenceMan\task\async
 * @author Jan Sohn / xxAROX
 * @date 12. Juli, 2023 - 14:35
 * @ide PhpStorm
 * @project pmmp
 */
class FetchGatewayInformationTask extends AsyncTask{
	private const URL = "https://raw.githubusercontent.com/Presence-Man/releases/main/gateway.json";

	public function onRun(): void{
		$response = Internet::getURL(self::URL);
		try {
			if ($response == null) {
				PresenceMan::getInstance()->getLogger()->critical("Presence-Man backend-gateway config is not reachable, disabling..");
				Server::getInstance()->getPluginManager()->disablePlugin(PresenceMan::getInstance());
			}
			$json = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
			if ($response->getCode() != 200) throw new InternetException($json["message"] ?? "Couldn't fetch gateway data");
			$this->setResult($json);
		} catch (JsonException $e) {
			GlobalLogger::get()->logException(new InternetException("Error while fetching gateway information: {$e->getMessage()}"));
			$this->setResult(null);
		}
	}
	public function onCompletion(): void{
		$result = $this->getResult();
		if ($result == null) {
			PresenceMan::getInstance()->getLogger()->warning("Couldn't fetch gateway data!");
			return;
		}
		Gateway::$protocol = ((string) $result["protocol"]) ?? Gateway::$protocol;
		Gateway::$address = ((string) $result["address"]) ?? Gateway::$address;
		Gateway::$port = ((int) $result["port"]) ?? Gateway::$port;
		self::ping_backend(function (bool $success): void{
			if (!$success) PresenceMan::getInstance()->getLogger()->error("Error while connecting to backend-server!");
		});
	}

	/**
	 * Function ping_backend
	 * @param Closure<bool> $callback
	 * @return void
	 * @internal
	 */
	public static function ping_backend(Closure $callback): void{
		if (ReconnectingTask::$active) return;
		Server::getInstance()->getAsyncPool()->submitTask(new class(Gateway::getUrl(), $callback) extends AsyncTask{
			public function __construct(private string $url, private Closure $callback){
			}
			public function onRun(): void{
				try {
					$result = Internet::getURL($this->url);
					$this->setResult($result != null && $result->getCode() == 200);
				} catch (InternetException $e) {
					$this->setResult(false);
				}
			}
			public function onCompletion(): void{
				$success = $this->getResult();
				if (!$success) {
					Gateway::$broken = true;
					ReconnectingTask::activate();
				} else {
					ReconnectingTask::deactivate();
					PresenceMan::getInstance()->getLogger()->notice("This server will be displayed as '" . PresenceMan::$SERVER . "' on '" . PresenceMan::$NETWORK . "' network in presences!");
				}
				($this->callback)($success);
			}
		});
	}
}
