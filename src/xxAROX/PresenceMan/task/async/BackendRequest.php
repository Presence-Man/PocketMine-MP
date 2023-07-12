<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\task\async;
use Closure;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\Utils;
use Throwable;
use xxAROX\PresenceMan\entity\ApiRequest;
use xxAROX\PresenceMan\entity\Gateway;
use xxAROX\PresenceMan\PresenceMan;


/**
 * Class BackendRequest
 * @package xxAROX\PresenceMan\task\async
 * @author Jan Sohn / xxAROX
 * @date 12. Juli, 2023 - 14:57
 * @ide PhpStorm
 * @project Presence-Man | PocketMine-MP
 */
class BackendRequest extends AsyncTask{
	protected string $url;

	/**
	 * BackendRequest constructor.
	 * @param ApiRequest $request
	 * @param Closure<array> $onResponse
	 * @param Closure<InternetRequestResult> $onError
	 * @param int $timeout
	 */
	public function __construct(private ApiRequest $request, private Closure $onResponse, private Closure $onError, private int $timeout = 5){
		$this->url = Gateway::getUrl();
		Utils::validateCallableSignature(function (array $response): void{}, $this->onResponse);
		Utils::validateCallableSignature(function (InternetRequestResult $response): void{}, $this->onError);
	}

	public function onRun(): void{
		if (!Internet::$online) throw new InternetException("Cannot execute web request while offline");
		$headers = [];
		foreach ($this->request->getHeaders() as $hk => $hv) $headers[] = $hk . ": " . $hv;
		$ch = curl_init($this->url . $this->request->getUri());

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, (int) ($this->timeout * 1000));
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, (int) ($this->timeout * 1000));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(["Content-Type: application/json"], $headers));
		curl_setopt($ch, CURLOPT_HEADER, true);

		if ($this->request->isPostMethod()) {
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->request->getBody()));
		}
		try {
			$raw = curl_exec($ch);
			if ($raw === false) throw new InternetException(curl_error($ch));
			if (!is_string($raw)) throw new AssumptionFailedError("curl_exec() should return string|false when CURLOPT_RETURNTRANSFER is set");
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (!is_int($httpCode)) throw new AssumptionFailedError("curl_getinfo(CURLINFO_HTTP_CODE) always returns int");
			$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$rawHeaders = substr($raw, 0, $headerSize);
			$body = substr($raw, $headerSize);
			$headers = [];
			foreach (explode("\r\n\r\n", $rawHeaders) as $rawHeaderGroup) {
				$headerGroup = [];
				foreach (explode("\r\n", $rawHeaderGroup) as $line){
					$nameValue = explode(":", $line, 2);
					if (isset($nameValue[1])) $headerGroup[trim(strtolower($nameValue[0]))] = trim($nameValue[1]);
				}
				$headers[] = $headerGroup;
			}
			$this->setResult(new InternetRequestResult($headers, $body, $httpCode));
		} catch (InternetException $e) {
			if (str_starts_with($e->getMessage(), "Failed to connect to ")) throw new InternetException("Failed to connect to " . $this->url . $this->request->getUri());
		} finally {
			curl_close($ch);
		}
	}

	public function onCompletion(): void{
		/** @var InternetRequestResult $result */
		if (!is_null($result = $this->getResult())) {
			if (in_array($result->getCode(), range(100, 399))) { // Good
				try {
					$result = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
					($this->onResponse)($result);
				} catch (Throwable $e) {
					PresenceMan::getInstance()->getLogger()->error($this->url . $this->request->getUri());
					PresenceMan::getInstance()->getLogger()->logException($e);
				}
			} else if (in_array($result->getCode(), range(400, 499))) { // Client-Errors
				PresenceMan::getInstance()->getLogger()->error("[CLIENT-ERROR] [" .$this->request->getUri() . "]: " . $result->getBody());
				($this->onError)($result);
			} else if (in_array($result->getCode(), range(500, 599))) { // Server-Errors
				PresenceMan::getInstance()->getLogger()->error("[API-ERROR] [" .$this->request->getUri() . "]: " . $result->getBody());
				($this->onError)($result);
			}
		} else {
			PresenceMan::getInstance()->getLogger()->error("[JUST-IN-CASE-ERROR] [" . $this->request->getUri() . "]: got null, that's not good");
		}
	}
}