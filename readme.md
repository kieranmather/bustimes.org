## Bustimes.org [![SensioLabsInsight](https://insight.sensiolabs.com/projects/9842b49c-8c65-486c-adb3-e4dd9bb810bc/mini.png)](https://insight.sensiolabs.com/projects/9842b49c-8c65-486c-adb3-e4dd9bb810bc) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kieranmather/bustimes.org/badges/quality-score.png?s=a1af3cd19b10e3ce870481624d3994fecef1f5a8)](https://scrutinizer-ci.com/g/kieranmather/bustimes.org/)

## Configuration

Add your username/password in app/config/traveline.php and add the base URL of TfL Countdown in there too. `composer install` and follow the error messages to get it working.

GTFS (currently only for TfGM) is stored in MySQL for relational goodness and NaPTAN is stored in Mongo because MongoDB is webscale. Redis is used to cache non-free or limited API requests like NextBuses. It's designed to be reasonably flexible due to the dissimilarity of the many transport APIs. Provided you can extract a timestamp, a heading and a name of a service you could integrate an API or dataset easily.
