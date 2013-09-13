<?php
/**
 * Request
 * Provide the Interface to create Requests
 * @author Guilherme Guimarães Viana <ggviana.hotmail.com.br>
 */
abstract class Request{

	/* Location to resource */
	private $url;
	
	/**
	 *	Constructs a Request
	 *	@param $url String Path to resource
	 */
	public function __construct($url = false){
		$this->url = $url;
	}
	
	/**
	 *	Returns request URL
	 *	@return String Request URL
	 */
	public final function getURL(){
		return $this->url;
	}
	
	/**
	 *	Sets the request URL
	 *	@param $url String New URL
	 */
	public final function setURL($url){
		$this->url = $url;
	}
}
?>