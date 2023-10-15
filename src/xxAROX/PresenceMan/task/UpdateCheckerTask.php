<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\task;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use xxAROX\PresenceMan\task\async\PerformUpdateTask;


/**
 * Class UpdateCheckerTask
 * @package xxAROX\PresenceMan\task
 * @author Jan Sohn / xxAROX
 * @date 15. Oktober, 2023 - 20:32
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
class UpdateCheckerTask extends Task{
	public static bool $running = false;

	public function __construct(){
	}

	public function onRun(): void{
		if (self::$running) return;
		self::$running = true;
		Server::getInstance()->getAsyncPool()->submitTask(new PerformUpdateTask());
	}
}
