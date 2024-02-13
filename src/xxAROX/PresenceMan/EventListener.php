<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan;
use ErrorException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use ReflectionClass;
use ReflectionException;
use xxAROX\PresenceMan\entity\ApiActivity;
use xxAROX\PresenceMan\utils\Utils;


/**
 * Class EventListener
 * @package xxAROX\PresenceMan
 * @author Jan Sohn / xxAROX
 * @date 12. Juli, 2023 - 16:01
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
class EventListener implements Listener{
	/**
	 * Function PlayerLoginEvent
	 * @param PlayerLoginEvent $event
	 * @return void
	 * @priority MONITOR
	 */
	public function PlayerLoginEvent(PlayerLoginEvent $event): void{
		if (Utils::isFromSameHost($event->getPlayer()->getNetworkSession()->getIp())) return;
		if (!PresenceMan::$ENABLE_DEFAULT) return;
		if (!PresenceMan::$UPDATE_SKIN) PresenceMan::save_skin($event->getPlayer(), $event->getPlayer()->getSkin());
		PresenceMan::setActivity($event->getPlayer(), ApiActivity::default_activity());
	}

	/**
	 * Function PlayerChangeSkinEvent
	 * @param PlayerChangeSkinEvent $event
	 * @return void
	 * @priority MONITOR
	 */
	public function PlayerChangeSkinEvent(PlayerChangeSkinEvent $event): void{
		if (Utils::isFromSameHost($event->getPlayer()->getNetworkSession()->getIp())) return;
		if (!$event->isCancelled()) PresenceMan::save_skin($event->getPlayer(), $event->getPlayer()->getSkin());
	}

	/**
	 * Function PlayerQuitEvent
	 * @param PlayerQuitEvent $event
	 * @return void
	 */
	public function PlayerQuitEvent(PlayerQuitEvent $event): void{
		if (Utils::isFromSameHost($event->getPlayer()->getNetworkSession()->getIp())) return;
		unset(PresenceMan::$presences[$event->getPlayer()->getXuid()]);
		PresenceMan::offline($event->getPlayer());
	}
}
