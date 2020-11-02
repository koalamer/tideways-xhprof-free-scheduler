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

class TidewaysXhprofFreeScheduler {

	private static $singleton;
	private static $startingChanceDivisor = 10000;
	private static $segmentation = [];

	public static function init(): bool{
		if (!extension_loaded('tideways_xhprof')) {
			return false;
		}

		if (self::$singleton !== null) {
			return true;
		}

		if (self::$startingChanceDivisor > 1) {
			srand((int)(microtime(true) * 1000));
			$luckyNumber = rand(1, self::$startingChanceDivisor);
			if ($luckyNumber !== 1){
				return true;
			}	
		}

		tideways_xhprof_enable(TIDEWAYS_XHPROF_FLAGS_CPU | TIDEWAYS_XHPROF_FLAGS_MEMORY);
		self::$singleton = new self();
		return true;
	}

	public static function setStartingChanceDivisor(int $divisor){
		self::$startingChanceDivisor = $divisor;
	}

	public static function setSegmentation(string $key, string $value){
		if( strlen($value) ){
			self::$segmentation[$key] = $value;
			return;
		}

		self::$segmentation[$key] = 'empty';
	}

	function __destruct() {
		$logPath = $this->getLogPath();

		if (!file_exists($logPath)){
			mkdir($logPath, 0777, true);
		}
		
		if (!is_dir($logPath)) {
			return;	
		}

		$logFileName = $logPath . DIRECTORY_SEPARATOR . (new \DateTime())->format('Ymd_His_u') . '.json';
		$data = tideways_xhprof_disable();

		file_put_contents($logFileName, json_encode($data));
	}

	private function getLogPath() : string {
		$segmentation = self::$segmentation;
		ksort($segmentation);
		return implode(DIRECTORY_SEPARATOR, $segmentation);
	}
}
