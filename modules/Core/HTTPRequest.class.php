<?php
/**
 * HTTPRequest
 * Represents a request using the Hyper text transfer protocol
 * @author Guilherme Guimarães Viana <ggviana.hotmail.com.br>
 */
class HTTPRequest extends Request{
	
	/* Array of Strings that is sent to the server with the request */
	private data;
	
	/**
	 *	Constructs a HTTPRequest
	 *	@param $url String Path to resource
	 */
	public function __construct($url){
		parent::__construct($url);
	}
	
	public function setData($data){
		$this->data = $data;
	}
	
	public function getData($data){
		return $this->data;
	}
	
	public function post($data = false){
		if($data !== false){
			$this->setData($data);
		}
		
	}
	
	public function get($data = false){
		if($data !== false){
			$this->setData($data);
		}
		
	}
	
	public function put(){}
	public function delete(){}
}
?>