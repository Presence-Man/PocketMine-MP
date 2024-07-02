# How to use custom presences?


### Configuration
> Please take a look over [config.yml](resources/config.yml), there is everything you need!


### Set custom presence:
```php
/** @var Player $player */
use xxAROX\PresenceMan\entity\ApiActivity;
use xxAROX\PresenceMan\PresenceMan;

$endsIn15min_activity = ApiActivity::ends_in(time() + (60 * 15));
PresenceMan::setActivity($player, $endsIn15min_activity);

$playersLeft_and_endsIn15min_activity = ApiActivity::players_left(8, 12, $endsIn15min_activity);
PresenceMan::setActivity($player, $playersLeft_and_endsIn15min_activity);

```

### Object orientated:
```php
use xxAROX\PresenceMan\entity\ApiActivity;
use xxAROX\PresenceMan\PresenceMan;

/** @var \pocketmine\player\Player $player */
/** @var int $ends_at */
/** @var int $players_left */
$bedwars_activity = new ApiActivity(
	\xxAROX\PresenceMan\entity\ActivityType::PLAYING,
	"4x1", "Bedwars", 
	$ends_at,
	"bedwars",
	"Bewdars - 4x1",
	4, $players_left
)
PresenceMan::setActivity($player, $bedwars_activity);

```


### Get head url: (only works for Presence-Man players)
```php
use xxAROX\PresenceMan\PresenceMan;
/** @var \pocketmine\player\Player $player */
PresenceMan::getHeadUrl($player->getXuid()); // => http(s)://<gateway>/api/v<version>/heads/<xuid>

```
