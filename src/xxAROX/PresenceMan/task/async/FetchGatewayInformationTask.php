<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\task\async;
use GlobalLogger;
use JsonException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use xxAROX\PresenceMan\entity\Gateway;
use xxAROX\PresenceMan\PresenceMan;


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
			if ($response->getCode() != 200) throw new InternetException($json["message"] ?? "{$response->getCode()} status code");
			$this->setResult($json);
		} catch (JsonException $e) {
			GlobalLogger::get()->logException(new InternetException("Error while fetching gateway information: {$e->getMessage()}"));
		}
	}
	public function onCompletion(): void{
		$result = $this->getResult();
		Gateway::$protocol = ((string) $result["protocol"]) ?? Gateway::$protocol;
		Gateway::$address = ((string) $result["address"]) ?? Gateway::$address;
		Gateway::$port = ((int) $result["port"]) ?? Gateway::$port;
		Server::getInstance()->getAsyncPool()->submitTask(new class(Gateway::getUrl()) extends AsyncTask{
			public function __construct(private string $url){
			}
			public function onRun(): void{
				try {
					$result = Internet::getURL($this->url);
					if ($result != null && $result->getCode() == 200) $this->setResult(true);
					else $this->setResult(false);
				} catch (InternetException $e) {
					$this->setResult(false);
				}
			}
			public function onCompletion(): void{
				$success = $this->getResult();
				if (!$success) {
					Gateway::$broken = true;
					PresenceMan::getInstance()->getLogger()->critical("Presence-Man backend-server is not reachable, disabling..");
					Server::getInstance()->getPluginManager()->disablePlugin(PresenceMan::getInstance());
				} else {
					PresenceMan::getInstance()->getLogger()->info("Presence-Man backend located at: " . Gateway::getUrl());
					PresenceMan::getInstance()->getLogger()->notice("This server will be displayed as " . PresenceMan::$SERVER . " on " . PresenceMan::$NETWORK . " in presences!");
				}
			}
		});
	}
}
