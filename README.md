# tideways-xhprof-free-scheduler

A small helper class to integrate Tideway's Xhprof free edition into your code.

## How to Use

### Use the class name in your script

You can have the class autoloaded, but if you want to include the autoloading routine in the measurement, you should include the source file directly and start the profiling before including the autoloader.

```php
use koalamer\Tideways\TidewaysXhprofFreeScheduler;
```

### Set how often to start profiling

Setting the value X will result in a 1 in X chance for the profiling to start. Setting it to less than 1 rwsults in never starting the profiler.
The default value is 0, which means no profiling.

```php
TidewaysXhprofFreeScheduler::setStartingChanceDivisor(100);
```

### Start the actual profiling

The init() call will use the current value of the starting chance divisor to determine whether to actually start profiling or not.

Once the profiling was started, subsequent calls return without doing anything.

For example: in a single entry point scenario you can set a low starting chance globally, call init(), and later set a higher starting chance for code (module/routine/action) you know run less often, and call init() again. This way you can capture profile information on parts of your code that would otherwise have a low chance of being profiled, but still have a global setting, without the two interfering with eachother.

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

The log file name itself will be the current time in the form of date, time and microsecs like this: "20201103_064423_032472.json". This json serialization is what the [Tideways toolkit](https://github.com/tideways/toolkit) needs to operate, that's why it was chosen.

The log path directories will be created as needed to house the log file.
