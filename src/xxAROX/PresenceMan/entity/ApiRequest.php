<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\entity;
/**
 * Class ApiRequest
 * @package xxAROX\PresenceMan\entity
 * @author Jan Sohn / xxAROX
 * @date 12. Juli, 2023 - 15:18
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
final class ApiRequest{
	private array $headers = [
		"Content-Type" => "application/json"
	];
	private array $body;
	private bool $post_method;

	/** @internal */
	static string $URI_UPDATE_PRESENCE = "/api/v1/servers/update_presence";
	/** @internal */
	static string $URI_UPDATE_HEAD = "/api/v1/servers/update_head";
	/** @internal */
	static string $URI_OFFLINE = "/api/v1/servers/offline";

	/**
	 * ApiRequest constructor.
	 * @param string $uri
	 * @param array $body
	 * @param bool $post_method
	 */
	public function __construct(private string $uri, array $body = [], bool $post_method = false){
		$this->body = $body;
		$this->post_method = $post_method;
	}

	public function serialize(): string{
		$arr = [
			"uri" => $this->uri,
			"headers" => $this->headers,
			"body" => $this->body,
			"post_method" => $this->post_method,
		];
		return json_encode($arr);
	}

	public static function deserialize(string $str): self{
		$json = json_decode($str, true);
		$self = new self($json["uri"], ($json["body"] ?? []), (bool) $json["post_method"], );
		$self->headers = $json["headers"] ?? [];
		return $self;
	}

	/**
	 * Function getUri
	 * @return string
	 */
	public function getUri(): string{
		return $this->uri;
	}

	/**
	 * Function header
	 * @param string $key
	 * @param string $value
	 * @return $this
	 */
	public function header(string $key, string $value): self{
		$this->headers[$key] = $value;
		return $this;
	}

	/**
	 * Function body
	 * @param string $key
	 * @param string $value
	 * @return $this
	 */
	public function body(string $key, string $value): self{
		$this->body[$key] = $value;
		return $this;
	}

	/**
	 * Function getHeaders
	 * @return array
	 */
	public function getHeaders(): array{
		return $this->headers;
	}

	/**
	 * Function getBody
	 * @return array
	 */
	public function getBody(): array{
		return $this->body;
	}

	/**
	 * Function isPostMethod
	 * @return bool
	 */
	public function isPostMethod(): bool{
		return $this->post_method;
	}
}
