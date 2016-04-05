**I no longer own or run bustimes.org, this code will not be updated.**

## Bustimes.org

## Configuration

Add your username/password in app/config/traveline.php and add the base URL of TfL Countdown in there too. `composer install` and follow the error messages to get it working.

## Info

Both NaPTAN and GTFS are stored in Postgres. Redis is used as a cache for the Traveline API as it's slow (2500ms vs 100ms for a GTFS query). London's API is lovely and needs no faffing about. NaPTAN is filtered using the national unused bus stops database- all stopping points are contained within the database but (in theory) only bus stops will be marked as `inuse`.
