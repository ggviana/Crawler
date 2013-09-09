<?php
/**
 * Timer Class
 * Count a time span
 * @author Guilherme Guimarães Viana <ggviana.hotmail.com.br>
 */
class Timer{
	
	const OUTPUT_MICROSECONDS = 1000000;
	const OUTPUT_MILLISECONDS = 1000;
	const OUTPUT_SECONDS = 1;
	const OUTPUT_MINUTES = 60;
	const OUTPUT_HOURS = 3600;
	
	private $outputFormat;
	/* Stores starting time */
	private $startTime;
	/* Stores ending time */
	private $endTime;
	
	public function __construct($outputFormat = false){
		if($outputFormat !== false and $this->isATimeUnitConstant($outputFormat)){
			$this->outputFormat = $outputFormat;
		}else{
			$this->outputFormat = self::OUTPUT_SECONDS;
		}
		$this->start();
	}
	
	/**
	 *	Starts counting time
	 */
	public function start(){
		$this->startTime = $this->getCurrentTimeInSeconds();
	}
	
	/**
	 *	Stops counting time
	 */
	public function end(){
		$this->endTime = $this->getCurrentTimeInSeconds();
	}
	
	private function getCurrentTimeInSeconds(){
		$mtime = microtime(); 
		$mtime = explode(" ",$mtime); 
		$mtime = $mtime[1] + $mtime[0]; 
		return $mtime;
	}
	
	/**
	 *	Stops counting time
	 *	@return double Execution time
	 */
	public function getExecutionTime(){
		$this->end();
		if($this->isMicrosecondsOrMilliseconds($this->outputFormat)){
			return(($this->endTime - $this->startTime)*$this->outputFormat);
		}
		return(($this->endTime - $this->startTime)/$this->outputFormat);
	}
	
	private function isATimeUnitConstant($outputFormat){
		return (
		$outputFormat === self::OUTPUT_MICROSECONDS or
		$outputFormat === self::OUTPUT_MILLISECONDS or
		$outputFormat === self::OUTPUT_SECONDS or
		$outputFormat === self::OUTPUT_MINUTES or
		$outputFormat === self::OUTPUT_HOURS);
	}
	
	private function isMicrosecondsOrMilliseconds($outputFormat){
		return (
		$outputFormat === self::OUTPUT_MICROSECONDS or
		$outputFormat === self::OUTPUT_MILLISECONDS);
	}
}
?>