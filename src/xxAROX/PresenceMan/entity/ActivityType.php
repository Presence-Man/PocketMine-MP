<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\entity;
use JetBrains\PhpStorm\Pure;
use pocketmine\utils\EnumTrait;


/**
 * Class ActivityType
 * @package xxAROX\PresenceMan\entity
 * @author Jan Sohn / xxAROX
 * @date 15. Juli, 2023 - 00:02
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see building/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static ActivityType COMPETING()
 * @method static ActivityType CUSTOM()
 * @method static ActivityType LISTENING()
 * @method static ActivityType PLAYING()
 * @method static ActivityType STREAMING()
 * @method static ActivityType UNUSED()
 */
class ActivityType{
	use EnumTrait{
		__construct as _enum___construct;
	}

	protected static function setup(): void{
		self::_registryRegister("PLAYING", new self("PLAYING"));
		self::_registryRegister("STREAMING", new self("STREAMING"));
		self::_registryRegister("LISTENING", new self("LISTENING"));
		self::_registryRegister("UNUSED", new self("UNUSED"));
		self::_registryRegister("CUSTOM", new self("CUSTOM"));
		self::_registryRegister("COMPETING", new self("COMPETING"));
	}

	#[Pure]
	public function __toString(): string{
		return $this->name();
	}
}
