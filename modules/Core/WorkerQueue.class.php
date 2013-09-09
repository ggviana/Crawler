<?php
/**
 * Worker queue Class
 * Manipulate Crawler workers
 * @author Guilherme Guimarães Viana <ggviana.hotmail.com.br>
 */
class WorkerQueue{
	
	/* How many times should it retry when a worker fails */
	public $retry;
	/* Workers queue */
	private $workers;
	
	/**
	 *	Adds a worker to the queue
	 *	@param $url = URL to be fetched
	 *	@param $options = Curl options
	 *	@param $alias = Optional reference name
	 */
	public function addWorker($url, $options = false, $alias = false){
		if($alias === false){			
			$this->workers[] = curl_init($url);
			
			if($options !== false){
				$workerName = count($this->workers) - 1;
				$this->setOptions($options, $workerName);
			}
		}
		else{
			$this->workers[$alias] = curl_init($url);
			if($options !== false){
				$this->setOptions($options, $alias);
			}
		}
	}
	
	/**
	 *	Set multiple options to a worker
	 *	@param $options = Array options
	 *	@param $workerName = integer|String Reference to the worker
	 */
	public function setOptions($options, $workerName){
		curl_setopt_array($this->workers[$workerName], $options);
	}
	
	/**
	 *	Set a option to a worker
	 *	@param $option = Option name
	 *	@param $value = Option value
	 *	@param $workerName = integer|String Reference to the worker
	 */
	public function setOption($option, $value, $workerName){
		curl_setopt($this->workers[$workerName], $option, $value);
	}
	
	/**
	 *	Returns information about a specific worker in the queue.	 
	 *	If a worker reference is passed, information about this worker, else information about all workers.
	 *	@param $workerName = integer|String Reference to the worker
	 *	@param $option = Option name
	 *	@return mixed 
	 */
	public function getInfo($workerName = false, $option = false){
		$info = array();
		if($workerName === false){
			foreach($this->workers as $worker){
				$info[] = ($option === false)? 
					curl_getinfo($this->workers[$workerName]) : 
					curl_getinfo($this->workers[$workerName],$option);
			}
		}
		else{
			$info = ($option === false)? 
				curl_getinfo($this->workers[$workerName]) : 
				curl_getinfo($this->workers[$workerName],$option);
		}
		return $info;
	}
	
	/**
	 *	Starts the queue execution.
	 *	If a worker reference is passed, execute this worker, else execute all workers.
	 *	@param $workerName = integer|String Reference to the worker = integer|String Reference to the worker
	 *	@return String Response contents
	 */
	public function execute($workerName = false){
		return ($workerName === false) ?
			$this->executeAll() :
			$this->executeSingle($workerName);
	}
	
	/**
	 *	Execute a worker
	 *	@param $workerName = integer|String Reference to the worker
	 *	@return String Response contents
	 */
	public function executeSingle($workerName){
		$retry = $this->retry;
		
		if($retry > 0){
			do{
				$result = curl_exec($this->workers[$workerName]);
				$http_status = curl_getinfo($this->workers[$workerName], CURLINFO_HTTP_CODE);
				$retry--;
			}while($retry >= 0 and ((int)$http_status != 200));
		}
		else{
			$result = curl_exec($this->workers[$workerName]);
		}
		
		return $result;
	}
	
	/**
	 *	Execute all worker in the queue
	 *	@return Array Responses contents
	 */
	public function executeAll(){
		
		// Adding workers in a multi-workerArray
		$mh = curl_multi_init();
		
		foreach($this->workers as $workerName => $worker){
			curl_multi_add_handle($mh, $worker);
		}
		
		// Executing all workers
		$active = null;
		do{
			$mrc = curl_multi_exec($mh, $active);
		}while($mrc == CURLM_CALL_MULTI_PERFORM);
		
		while ($active && $mrc == CURLM_OK){
			if (curl_multi_select($mh) != -1 ){
				do{
					$mrc = curl_multi_exec($mh, $active);
				}while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		
		if ($mrc != CURLM_OK){
			throw new Exception("Multi execution read error {$mrc}.\n");
		}
		
		// Fetching results and removing handlers
		$results = array();
		foreach ($this->workers as $workerName => $worker){
			$http_status = $this->getInfo($workerName, CURLINFO_HTTP_CODE );
			if($http_status > 0 && $http_status < 400){
				$results[] = curl_multi_getcontent($worker);
			}
			else{
				$result = null;
				$retry = $this->retry;
				if($retry > 0){
					do{
						$result = $this->executeSingle($workerName);
						$http_status = curl_getinfo($result, CURLINFO_HTTP_CODE);
						$retry--;
					}while($retry >= 0 and ((int)$http_status != 200));
				}
				$results[] = $result ? $result : false;
			}
			curl_multi_remove_handle($mh, $worker);
		}
		curl_multi_close($mh);
		return $results;
	}
	
	/**
	 *	Removes workers
	 *	If a worker reference is passed, removes this worker, else removes all workers.
	 *	@param $workerName = integer|String Reference to the worker
	 */
	public function removeWorker($workerName = false){
		if($workerName === false){
			foreach($this->workers as $workerName => $worker){
				curl_close($this->workers[$workerName]);
				unset($this->workers[$workerName]);
			}
		}
		else{
			curl_close($this->workers[$workerName]);
			unset($this->workers[$workerName]);
		}
	}
	
	/**
	 *	Returns execution error
	 *	If a worker reference is passed, returns execution errors of this worker, else returns execution errors of all workers.
	 *	@param $workerName = integer|String Reference to the worker
	 *	@return mixed Error contents
	 */
	public function getErrors($workerName = false){
		$errors = array();
		if($workerName === false){
			foreach($this->workers as $workerName => $worker){
				$errors[] = curl_error($this->workers[$workerName]);
			}
		}
		else{
			$errors = curl_error($this->workers[$workerName]);
		}
		return $errors;
	}
	
	/**
	 *	Returns error number
	 *	If a worker reference is passed, returns error number of this worker, else returns error number of all workers.
	 *	@param $workerName = integer|String Reference to the worker
	 *	@return mixed Error numbers
	 */
	public function getErrorNumber($workerName = false){
		$errors = array();
		if($workerName === false){
			foreach($this->workers as $worker){
				$errors[] = curl_errno($worker);
			}
		}
		else{
			$errors = curl_errno($this->workers[$workerName]);
		}
		return $errors;
	}
}
