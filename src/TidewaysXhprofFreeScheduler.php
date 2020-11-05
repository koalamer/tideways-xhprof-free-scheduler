<?php
/*
MIT License

Copyright (c) 2020 https://github.com/koalamer

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace koalamer\Tideways;

class TidewaysXhprofFreeScheduler
{

	private static $singleton;
	private static $startingChanceDivisor = 0;
	private static $segmentation = [];

	/**
	 * Starts the profiling. If the starting chance divisor is less than 1, the
	 * profiling is not started. If this value is 1 or, let's say X, then
	 * the profiling is started with a 1 in X chance randomly.
	 * If the extension is not loaded, the call returns false, otherwise true.
	 * Multiple calls to init() are allowed, each call will evaluate the current
	 * starting chance divisor parameter anew. If an earlier call already
	 * started the profiling, the call returns without doing anything.
	 */
	public static function init(): bool
	{
		if (self::$startingChanceDivisor < 1) {
			return true;
		}

		if (!extension_loaded('tideways_xhprof')) {
			return false;
		}

		if (self::$singleton !== null) {
			return true;
		}

		if (self::$startingChanceDivisor > 1) {
			srand((int)(microtime(true) * 1000));
			$luckyNumber = rand(1, self::$startingChanceDivisor);
			if ($luckyNumber !== 1) {
				return true;
			}
		}

		tideways_xhprof_enable(TIDEWAYS_XHPROF_FLAGS_CPU | TIDEWAYS_XHPROF_FLAGS_MEMORY);
		self::$singleton = new self();
		return true;
	}

	/**
	 * Sets the likelyhood ratio with which to start the profiling when init()
	 * is called. Setting the value X will result in a 1 in X chance to start.
	 * If the set value is smaller than 1, the profiling is neveer started.
	 * If you don't set a value, the default of 0 is used.
	 */
	public static function setStartingChanceDivisor(int $divisor)
	{
		self::$startingChanceDivisor = $divisor;
	}

	/**
	 * Sets one level of segmentation for the resulting log file in the form of
	 * defining one directory level of the logging path.
	 * There is no default logging path, so you should call this function at
	 * least once to define the path where the log files should be put.
	 * The log file path is assembled when the profiling is stopped, so you can
	 * call this function as many times you want, even after init() was fired.
	 * If an empty value is provided, it will be replaced by the string "empty".
	 * When assembling the final log file path, the collection of segmentation
	 * levels is sorted by key and imploded.
	 */
	public static function setSegmentation(string $key, string $value)
	{
		if (strlen($value)) {
			self::$segmentation[$key] = $value;
			return;
		}

		self::$segmentation[$key] = 'empty';
	}

	function __destruct()
	{
		$logPath = $this->getLogPath();

		if (!file_exists($logPath)) {
			mkdir($logPath, 0777, true);
		}

		if (!is_dir($logPath)) {
			return;
		}

		$time = explode(' ', microtime(false));
		$logFileName = sprintf(
			"%s%s%s%06d%s",
			$logPath,
			DIRECTORY_SEPARATOR,
			date('Ymd_His_', $time[1]),
			(int)($time[0] * 1000000),
			'.json'
		);
		$data = tideways_xhprof_disable();

		file_put_contents($logFileName, json_encode($data));
	}

	private function getLogPath(): string
	{
		$segmentation = self::$segmentation;
		if (!count($segmentation)) {
			return '.';
		}

		ksort($segmentation);
		return implode(DIRECTORY_SEPARATOR, $segmentation);
	}
}
