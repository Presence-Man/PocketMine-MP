<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\utils;
use pocketmine\utils\Config;


/**
 * Class Utils
 * @package xxAROX\PresenceMan\utils
 * @author Jan Sohn / xxAROX
 * @date 15. Oktober, 2023 - 20:09
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
final class Utils{
	public static function getconfigvalue(Config $config, string $key, string $env = "", mixed $default = null): mixed{
		if (empty($env)) $env = strtoupper($key);
		if (!str_starts_with($env, "PRESENCE_MAN_")) $env = "PRESENCE_MAN_" . $env;
		/** @phpstan-ignore */
		return ((getenv($env) == false || empty(getenv($env))) ? $config->getNested($key, $default) : getenv($env));
	}
	public static function isFromSameHost(string $ip): bool{
		return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}
}
