<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\entity;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use xxAROX\PresenceMan\PresenceMan;


/**
 * Class ApiActivity
 * @package xxAROX\PresenceMan\entity
 * @author Jan Sohn / xxAROX
 * @date 13. Juli, 2023 - 00:06
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
final class ApiActivity{
	/**
	 * ApiActivity constructor.
	 * @param ActivityType $type
	 * @param null|string $state
	 * @param null|string $details
	 * @param null|int $end
	 * @param null|string $large_icon_key
	 * @param null|string $large_icon_text
	 * @param null|int $party_max_player_count
	 * @param null|int $party_player_count
	 */
	public function __construct(
		public ActivityType $type,
		public ?string $state,
		public ?string $details,
		public ?int $end = null,
		public ?string $large_icon_key = null,
		public ?string $large_icon_text = null,
		public ?int $party_max_player_count = null,
		public ?int $party_player_count = null
	){
	}

	public function serialize(): string{
		return json_encode($this->json_serialize());
	}

	#[Pure]
	#[ArrayShape([
		'type'                   => "string",
		'client_id'              => "null|string",
		'state'                  => "null|string",
		'details'                => "null|string",
		'end'                    => "null|int",
		'large_icon_key'         => "null|string",
		'large_icon_text'        => "null|string",
		'party_max_player_count' => "null|int",
		'party_player_count'     => "null|int",
	])] public function json_serialize(): array{
		return [
			'client_id' => PresenceMan::$CLIENT_ID,
			'type' => $this->type->value,
			'state' => $this->state,
			'details' => $this->details,
			'end' => $this->end,
			'large_icon_key' => $this->large_icon_key,
			'large_icon_text' => $this->large_icon_text,
			'party_max_player_count' => $this->party_max_player_count,
			'party_player_count' => $this->party_player_count,
		];
	}

	public static function deserialize(array $json): APIActivity{
		$type = isset($json['type']) ? (ActivityType::tryFrom(mb_strtoupper($json['type'])) ?? ActivityType::PLAYING) : ActivityType::PLAYING;
		$state = $json['state'] ?? null;
		$details = $json['details'] ?? null;
		$end = $json['end'] ?? null;
		$large_icon_key = $json['large_icon_key'] ?? null;
		$large_icon_text = $json['large_icon_text'] ?? null;
		$party_max_player_count = $json['party_max_player_count'] ?? null;
		$party_player_count = $json['party_player_count'] ?? null;
		return new APIActivity(
			$type,
			$state,
			$details,
			$end,
			$large_icon_key,
			$large_icon_text,
			$party_max_player_count,
			$party_player_count
		);
	}

	public static function default_activity(): self{
		return PresenceMan::$default;
	}

	public static function ends_in(int $time, ?ApiActivity $base = null): self{
		$default = $base ?? self::default_activity();
		$default->end = $time * 1000;
		return $default;
	}

	public static function players_left(int $current_players, int $max_players, ?ApiActivity $base = null): self{
		$default = $base ?? self::default_activity();
		$default->party_player_count = $current_players;
		$default->party_max_player_count = $max_players;
		return $default;
	}
}
