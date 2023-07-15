<?php
declare(strict_types=1);
namespace xxAROX\PresenceMan\task\async;
use Closure;
use pocketmine\scheduler\AsyncTask;
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
	private string $request;
	private ?Closure $onResponse;
	private ?Closure $onError;
	private int $timeout;
	protected string $url;

	/**
	 * BackendRequest constructor.
	 * @param string $request
	 * @param ?Closure<array> $onResponse
	 * @param ?Closure<string> $onError
	 * @param int $timeout
	 */
	public function __construct(string $request, ?Closure $onResponse = null, ?Closure $onError = null, int $timeout = 5){
		$this->url = Gateway::getUrl();
		$this->request = $request;
		if ($onResponse != null) Utils::validateCallableSignature(function (array $response): void{}, $onResponse);
		$this->onResponse = $onResponse;
		if ($onError != null) Utils::validateCallableSignature(function (InternetRequestResult $response): void{}, $onError);
		$this->onError = $onError;
		$this->timeout = $timeout;
	}

	public function onRun(): void{
		if (!Internet::$online) throw new InternetException("Cannot execute web request while offline");
		$headers = [];
		$request = ApiRequest::deserialize($this->request);
		foreach ($request->getHeaders() as $hk => $hv) $headers[] = $hk . ": " . $hv;
		var_dump($this->url . $request->getUri());
		$url = $this->url . $request->getUri();

		if ($request->isPostMethod()) {
			$result = Internet::postURL(
				$url,
				json_encode($request->getBody()),
				$this->timeout,
				$headers,
				$err
			);
		} else {
			$result = Internet::getURL(
				$url,
				$this->timeout,
				$headers,
				$err
			);
		}
		if ($result == null && $err) throw new InternetException($err);
		else $this->setResult($result);
	}

	public function onCompletion(): void{
		$request = ApiRequest::deserialize($this->request);
		/** @var InternetRequestResult $result */
		if (!is_null($result = $this->getResult())) {
			if (in_array($result->getCode(), range(100, 399))) { // Good
				try {
					$result = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
					if ($this->onResponse != null) ($this->onResponse)($result);
				} catch (Throwable $e) {
					PresenceMan::getInstance()->getLogger()->error($this->url . $request->getUri());
					PresenceMan::getInstance()->getLogger()->logException($e);
				}
			} else if (in_array($result->getCode(), range(400, 499))) { // Client-Errors
				PresenceMan::getInstance()->getLogger()->error("[CLIENT-ERROR] [" .$request->getUri() . "]: " . $result->getBody());
				if ($this->onError != null) ($this->onError)($result);
			} else if (in_array($result->getCode(), range(500, 599))) { // Server-Errors
				PresenceMan::getInstance()->getLogger()->error("[API-ERROR] [" .$request->getUri() . "]: " . $result->getBody());
				if ($this->onError != null) ($this->onError)($result);
			}
		} else {
			PresenceMan::getInstance()->getLogger()->error("[JUST-IN-CASE-ERROR] [" . $request->getUri() . "]: got null, that's not good");
		}
	}
}
