<?php
class CSVExporter extends Exporter{
	private $filename;
	private $fieldnames;
	private $buffer;
	
	public function __construct($filename, $fieldnames){
		$this->filename = $filename;
		$this->fieldnames = is_array($fieldnames)? $fieldnames : array();
	}
	
	public function append($array){
		if(is_array($array)){
			$this->buffer[] = $array;
		}
	}
	
	public function export($delimiter = ',', $enclosure = '"'){
		$file = fopen($filename,'w');
		fputcsv($file, $this->fieldnames, $delimiter, $enclosure);
		foreach($buffer as $line){
			fputcsv($file, $line, $delimiter, $enclosure);
		}
		fclose($file);
	}
}
?>