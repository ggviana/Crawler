<?php
final class CSVJoiner{
	
	public static final function joinFiles($fileArray, $filename, $fieldnames, $delimiter = ',', $enclosure = '"'){
		$fileR = fopen($filename,'w');
		if(is_array($fieldnames)){
			fputcsv($fileR, $fieldnames, $delimiter = ',', $enclosure = '"');
		}
		
		foreach ($fileArray as $file) {
			file_concat($file,$fileR);
		}
		fclose($fileR);
	}
	
	public static final function getFieldnames($filename, $delimiter = ','){
		$file = fopen($filename);
		$line = fgets($file);
		fclose($file);
		$fieldnames = explode($delimiter, $line);
		return $fieldnames;
	}
	
	private function file_concat($file_from, &$file_to){
		$file_from = fopen($file_from,'r');
		// Skips the first line(header)
		fgets($file_from);
		
		// Start writing file
		while(!feof($file_from)){
			fwrite($file_to,utf8_decode(fgets($file_from)."\n"));
		}
		fclose($file_from);
	}
}
?>