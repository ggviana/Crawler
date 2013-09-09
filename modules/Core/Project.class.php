<?php
/**
 * Project
 * Provide the Interface to create a Crawler Project
 * @author Guilherme Guimarães Viana <ggviana.hotmail.com.br>
 */
abstract class Project{

	/* Current Worker Queue */
	private $queue;
	/* Local configurations */
	private $config;
	
	/**
	 *	Constructs a Crawler Project
	 *	@param $configFile String Absolute path to ini file, 
	 */
	public function __construct($configFile = './config.ini'){
		/* Set the script to run indefinitely */
		set_time_limit(0);
		$this->queue = new WorkerQueue;
		$this->loadConfiguration($configFile);
	}
	
	/**
	 *	Returns current worker queue
	 *	@return WorkerQueue Current Worker Queue
	 */
	public final function getQueue(){
		return $this->queue;
	}
	
	/**
	 *	Loads Project configuration from a ini file
	 *	@param $iniFile String Absolute path to ini file
	 *	@return boolean True if loading was successful, false otherwise
	 */
	public final function loadConfiguration($iniFile){
		if(file_exists($iniFile)){			
			$config = parse_iniFile($iniFile);
			foreach($config as $optionName => $optionValue){
				$this->config[$optionName] = $optionValue;
			}
			return true;
		}
		return false;
	}
	
	/**
	 *	Retrieve a configuration option
	 *	@param $optionName String Option name
	 *	@return string Option value
	 */
	public final function getConfigurationOption($optionName){
		return isset($this->configs[$optionName]) ? $this->configs[$optionName] : null;
	}
	
	/**
	 *	Sets a configuration option
	 *	@param $optionName String Option name
	 *	@param $optionValue String Option value
	 *	@return void
	 */
	public final function setConfigurationOption($optionName, $optionValue){
		$this->configs[$optionName] = $optionValue;
	}
	
	/**
	 *	Login project
	 *	This method is intended to implement login logic.
	 *	If your project do not require login, overwrite this method returning true.
	 *	@return boolean True if login was sucessful, false otherwise.
	 */
	abstract public function login();
	
	/**
	 *	Extract content
	 *	This method is intended to implement the logic used to extract content.
	 *	@return mixed Content
	 */
	abstract public function extract();
	
	/**
	 *	Exports the project using exportation rules.
	 *	@return boolean True if export was sucessful, false otherwise.
	 */
	abstract public function export(Exporter $exporter);
}
?>