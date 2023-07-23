<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\task;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use xxAROX\PresenceMan\PresenceMan;
use xxAROX\PresenceMan\task\async\FetchGatewayInformationTask;


/**
 * Class ReconnectingTask
 * @package xxAROX\PresenceMan\task
 * @author Jan Sohn / xxAROX
 * @date 13. Juli, 2023 - 21:42
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
class ReconnectingTask extends Task{
	public static bool $active = false;
	private static ?TaskHandler $task = null;

	public static function activate(): void{
		if (self::$active) return;
		self::$active = true;
		self::$task = PresenceMan::getInstance()->getScheduler()->scheduleRepeatingTask(new self(), 20 * 5);
	}

	public static function deactivate(): void{
		if (!self::$active) return;
		self::$task->cancel();
		self::$task = null;
		self::$active = false;
	}


	public function onRun(): void{
		FetchGatewayInformationTask::ping_backend(function (bool $success): void{
			if ($success) {
				PresenceMan::getInstance()->getLogger()->debug("Reconnected!");
				self::deactivate();
			}
		});
	}
}
