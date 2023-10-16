<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\task\async;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use xxAROX\PresenceMan\PresenceMan;
use xxAROX\PresenceMan\task\UpdateCheckerTask;


/**
 * Class PerformUpdateTask
 * @package xxAROX\PresenceMan\task\async
 * @author Jan Sohn / xxAROX
 * @date 15. Oktober, 2023 - 20:33
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
class PerformUpdateTask extends AsyncTask{
	private const LATEST_VERSION_URL = "https://github.com/Presence-Man/releases/raw/main/version-pocketmine-mp.txt";

	private string $currentVersion;
	private bool $notified = false;

	public function __construct(){
		$this->currentVersion = PresenceMan::getInstance()->getDescription()->getVersion();
	}

	/**
	 * Function onRun
	 * @return void
	 */
	public function onRun(): void{
		$latest = Internet::getURL(self::LATEST_VERSION_URL);
		if (
			empty($latest)
			|| empty($latest->getBody())
			|| $latest->getCode() != 200
		) {
			$this->setResult(null);
			return;
		}
		$this->setResult(version_compare($this->currentVersion, $latest->getBody(), "<") ? $latest->getBody() : null);
	}

	public function onCompletion(): void{
		$latest = $this->getResult();
		if (!empty($latest)) {
			if (!$this->notified) {
				PresenceMan::getInstance()->getLogger()->warning("Your version of Presence-Man is out of date. To avoid issues please update it to the latest version!");
				PresenceMan::getInstance()->getLogger()->warning("Download: https://presence-man.com/downloads/pocketmine-mp");
				$this->notified = true;
			}
		}
		UpdateCheckerTask::$running = false;
	}
}
