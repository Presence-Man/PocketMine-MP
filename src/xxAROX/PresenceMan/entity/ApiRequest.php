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
	private array $headers = [];
	private array $body;
	private bool $post_method;

	static string $URI_CHECKOUT = "/";
	static string $URI_OFFLINE = "/server/offline";
	static string $URI_UPDATE_PRESENCE = "/server/update_presence";

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