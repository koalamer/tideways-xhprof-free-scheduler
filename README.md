# tideways-xhprof-free-scheduler

A small helper class to integrate Tideway's Xhprof free edition into your code.

## How to Use

### Use the class name in your script

You can have the class autoloaded, but if you want to include the autoloading routine in the measurement, you should include the source file directly and start the profiling before including the autoloader.

```php
use koalamer\Tideways\TidewaysXhprofFreeScheduler;
```

### Set how often to start profiling

Setting the value X will result in a 1 in X chance for the profiling to start. Setting it to 1 or smaller results in always starting the profiler.

The default value is 10 000, which shouldn't be a low enough number to not cause atrouble if you forget to set your own value in production.

```php
TidewaysXhprofFreeScheduler::setStartingChanceDivisor(100);
```

### Start the actual profiling

The init() call will use the starting chance to determine whether to actually start profiling or not. This call can be repeated as many times you want, each time the current starting chance divisor is used to evaluate whether to start profiling or not.
This means, you can set a lower starting chance globally, and later try a higher starting chance for scripts you know start less often and therefore would otherwise have a low chance to be profiled.

Once the profiling has started the call returns without doing anything.

If the tideways_xhprof extension is not loaded, the call returns false, otherwise true.

```php
TidewaysXhprofFreeScheduler::init()
```

### Set the log file path by defining segmentation dimensions

You can set the log file path by defining segmentation. Each level of segmentation corresponds to a directory level. The key-value pairs you provide are only evaluated when the profiling stops, so you can define and redefine segmentation levels throughout your code.

The profiling stops when the script shuts down. The collection of segmentation parameters is then sorted by key and imploded, so the keys you use matter.

If you provide an empty string as the value, that value will be replaced by the string "empty".

For example, defining these segmentations:

```php
TidewaysXhprofFreeScheduler::setSegmentation("1-host", gethostname())
TidewaysXhprofFreeScheduler::setSegmentation("3-module", "awesome-module")
TidewaysXhprofFreeScheduler::setSegmentation("2-day", date("Y-m-d"))
TidewaysXhprofFreeScheduler::setSegmentation("0-root","/tmp")
TidewaysXhprofFreeScheduler::setSegmentation("4-action","")
```

will result in a log file path "/tmp/some-hostname/2020-11-03/awesome-module/empty/".

### Writing out the log file

The profiling stops when the internal singleton class instance is destroyed, and thus it is automatic.

The log file name itself will be the current time in the form of date, time and microsecs like this: "20201103_064423_032472.json". This json serialization is what the [Tideways toolkit](https://github.com/tideways/toolkit) needs to operate.

The log path directories will be created as needed to house the log file.
