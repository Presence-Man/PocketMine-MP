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
