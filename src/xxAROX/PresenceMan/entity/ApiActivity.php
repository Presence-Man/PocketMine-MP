<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\entity;
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
	 * @param null|string $network
	 * @param null|string $server
	 * @param null|int $start
	 * @param null|int $end
	 * @param null|string $large_icon_key
	 * @param null|string $large_icon_text
	 * @param null|string $small_icon_key
	 * @param null|string $small_icon_text
	 * @param null|int $party_max_player_count
	 * @param null|int $party_player_count
	 */
	public function __construct(
		public ActivityType $type,
		public ?string $network,
		public ?string $server,
		public ?int $start = null,
		public ?int $end = null,
		public ?string $large_icon_key = null,
		public ?string $large_icon_text = null,
		public ?string $small_icon_key = null,
		public ?string $small_icon_text = null,
		public ?int $party_max_player_count = null,
		public ?int $party_player_count = null
	){
	}

	public function serialize(): string{
		$json = [
			'client_id' => PresenceMan::$CLIENT_ID,
			'type' => mb_strtoupper($this->type->__toString()),
			'network' => $this->network,
			'server' => $this->server,
			'start' => $this->start,
			'end' => $this->end,
			'large_icon_key' => $this->large_icon_key,
			'large_icon_text' => $this->large_icon_text,
			'small_icon_key' => $this->small_icon_key,
			'small_icon_text' => $this->small_icon_text,
			'party_max_player_count' => $this->party_max_player_count,
			'party_player_count' => $this->party_player_count,
		];
		return json_encode($json);
	}

	public static function deserialize(array $json): APIActivity{
		$type = isset($json['type']) ? (ActivityType::getAll()[mb_strtoupper($json['type'])] ?? ActivityType::PLAYING()) : ActivityType::PLAYING();
		$network = $json['network'] ?? null;
		$server = $json['server'] ?? null;
		$start = $json['start'] ?? null;
		$end = $json['end'] ?? null;
		$large_icon_key = $json['large_icon_key'] ?? null;
		$large_icon_text = $json['large_icon_text'] ?? null;
		$small_icon_key = $json['small_icon_key'] ?? null;
		$small_icon_text = $json['small_icon_text'] ?? null;
		$party_max_player_count = $json['party_max_player_count'] ?? null;
		$party_player_count = $json['party_player_count'] ?? null;
		return new APIActivity(
			$type,
			$network,
			$server,
			$start,
			$end,
			$large_icon_key,
			$large_icon_text,
			$small_icon_key,
			$small_icon_text,
			$party_max_player_count,
			$party_player_count
		);
	}

	public static function default_activity(): self{
		return new self(
			ActivityType::PLAYING(),
			"Playing on " . PresenceMan::$NETWORK,
			PresenceMan::$SERVER,
			time() * 1000,
			null,
			PresenceMan::$DEFAULT_LARGE_IMAGE_KEY,
			PresenceMan::$DEFAULT_LARGE_IMAGE_TEXT,
			PresenceMan::$DEFAULT_SMALL_IMAGE_KEY,
			PresenceMan::$DEFAULT_SMALL_IMAGE_TEXT
		);
	}

	public static function ends_in(int $time, ?ApiRequest $base = null): self{
		$default = $base ?? self::default_activity();
		$default->end = $time * 1000;
		return $default;
	}

	public static function players_left(int $current_players, int $max_players, ?ApiRequest $base = null): self{
		$default = $base ?? self::default_activity();
		$default->party_player_count = $current_players;
		$default->party_max_player_count = $max_players;
		return $default;
	}
}
