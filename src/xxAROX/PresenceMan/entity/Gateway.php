<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\entity;
use xxAROX\PresenceMan\task\async\FetchGatewayInformationTask;
/**
 * Class Gateway
 * @package xxAROX\PresenceMan\entity
 * @author Jan Sohn / xxAROX
 * @date 12. Juli, 2023 - 14:42
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
final class Gateway{
	public static string $protocol = "http://";
	public static string $address = "127.0.0.1";
	public static ?int $port = null;
	public static bool $broken = false;

	/**
	 * @throws \LogicException
	 */
	public static function getUrl(bool $again = false): ?string{
		if (Gateway::$broken) throw new \LogicException("Presence-Man Backend server is not reachable");
		return self::$protocol . self::$address . (!empty(self::$port) ? ":" . self::$port : "");
	}
}
