<?php
class XPATH{

	public function __construct($input){
		$this->document = new DOMDocument();
		$this->xpath = file_exists($input) ? $this->document->loadHTMLFile($input) : $this->document->loadHTML($input);
	}
	
	public function query($xpath){
		$queryResult = $this->xpath->query($xpath);
		$queryLength = $queryResult->length;
		$result = array();
		for($i = 0;$i<$queryLength;$i++){
			$result[] = $queryResult->item($i)->textContent;
		}
		return $result;
	}
}
?>