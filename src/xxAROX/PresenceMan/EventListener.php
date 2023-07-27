<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use xxAROX\PresenceMan\entity\ApiActivity;


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
		if (!PresenceMan::$ENABLE_DEFAULT) return;
		PresenceMan::setActivity($event->getPlayer(), ApiActivity::default_activity());
	}

	/**
	 * Function PlayerJoinEvent
	 * @param PlayerJoinEvent $event
	 * @return void
	 * @priority MONITOR
	 */
	public function PlayerJoinEvent(PlayerJoinEvent $event): void{
		if ($event->getPlayer()->getSkin() != null) PresenceMan::save_head($event->getPlayer(), $event->getPlayer()->getSkin());
	}

	/**
	 * Function PlayerChangeSkinEvent
	 * @param PlayerChangeSkinEvent $event
	 * @return void
	 * @priority MONITOR
	 */
	public function PlayerChangeSkinEvent(PlayerChangeSkinEvent $event): void{
		if (!$event->isCancelled()) PresenceMan::save_head($event->getPlayer(), $event->getNewSkin());
	}

	/**
	 * Function PlayerQuitEvent
	 * @param PlayerQuitEvent $event
	 * @return void
	 */
	public function PlayerQuitEvent(PlayerQuitEvent $event): void{
		unset(PresenceMan::$presences[$event->getPlayer()->getXuid()]);
		PresenceMan::offline($event->getPlayer());
	}
}
