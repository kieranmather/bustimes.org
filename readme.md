## Bustimes.org [![SensioLabsInsight](https://insight.sensiolabs.com/projects/9842b49c-8c65-486c-adb3-e4dd9bb810bc/mini.png)](https://insight.sensiolabs.com/projects/9842b49c-8c65-486c-adb3-e4dd9bb810bc) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kieranmather/bustimes.org/badges/quality-score.png?s=a1af3cd19b10e3ce870481624d3994fecef1f5a8)](https://scrutinizer-ci.com/g/kieranmather/bustimes.org/)

## Configuration

Add your username/password in app/config/traveline.php and add the base URL of TfL Countdown in there too. `composer install` and follow the error messages to get it working.

## Info

Both NaPTAN and GTFS are stored in Postgres. Redis is used as a cache for the Traveline API as it's slow (2500ms vs 100ms for a GTFS query). London's API is lovely and needs no faffing about. NaPTAN is filtered using the national unused bus stops database- all stopping points are contained within the database but (in theory) only bus stops will be marked as `inuse`.