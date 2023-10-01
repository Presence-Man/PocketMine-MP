<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\entity;


/**
 * Class ActivityType
 * @package xxAROX\PresenceMan\entity
 * @author Jan Sohn / xxAROX
 * @date 15. Juli, 2023 - 00:02
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
enum ActivityType: string {
	case COMPETING = "COMPETING";
	case LISTENING = "LISTENING";
	case PLAYING = "PLAYING";
	case STREAMING = "STREAMING";
	case UNUSED = "UNUSED";
}