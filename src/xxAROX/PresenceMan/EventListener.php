<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;
use xxAROX\PresenceMan\entity\ApiActivity;
use xxAROX\PresenceMan\entity\ApiRequest;
use xxAROX\PresenceMan\task\async\BackendRequest;


/**
 * Class EventListener
 * @package xxAROX\PresenceMan
 * @author Jan Sohn / xxAROX
 * @date 12. Juli, 2023 - 16:01
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
class EventListener implements Listener{
	public function PlayerLoginEvent(PlayerLoginEvent $event): void{
		PresenceMan::setActivity($event->getPlayer(), ApiActivity::default_activity());
	}
	public function PlayerQuitEvent(PlayerQuitEvent $event): void{
		$ip = $event->getPlayer()->getNetworkSession()->getIp();
		unset(PresenceMan::$presences[$event->getPlayer()->getXuid()]);
		$request = new ApiRequest(ApiRequest::$URI_OFFLINE, [
			"ip" => $ip,
			"xuid" => $event->getPlayer()->getXuid(),
			"api_activity" => null
		]);
		Server::getInstance()->getAsyncPool()->submitTask(new BackendRequest(
			$request,
			function (array $response): void{
			},
			function (string $error): void{
			}
		));
	}
}
