<?php
/*
	TODO:
		envio de emails
		analise das páginas
*/

class robot{
	
	private $ch;
	private $user;
	private $pass;
	private $userfield;
	private $passfield;
	private $cookiefile;
	
	/**
		Inicializa um Robo a partir de um arquivo de configuracao
	*/
	function __construct($configfile){
		// Carregar configs
		$config = parse_ini_file($configfile);
		
		$this->user = $config['user'] ? $config['user']:null;
		$this->pass = $config['pass'] ? $config['pass']:null;
		$this->userfield = $config['userfield'] ? $config['userfield']:null;
		$this->passfield = $config['passfield'] ? $config['passfield']:null;
		$agent = $config['agent'] ? $config['agent']:null;
		$this->cookiefile = $config['cookiefile'] ? $config['cookiefile']:null;
		
		$this->ch = curl_init();
		// Configurando o cURL
		curl_setopt ($this->ch, CURLOPT_USERAGENT, $agent); // user-agent
		curl_setopt ($this->ch, CURLOPT_TIMEOUT, 60); // timeout
		curl_setopt ($this->ch, CURLOPT_FOLLOWLOCATION, 1); // permitir redirects?
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $this->cookiefile);
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $this->cookiefile);
	}
	
	/**
		Termina o uso do Robo e libera os recursos do sistema
	*/
	function terminate($boolean = FALSE){
		if($boolean === TRUE)
			clean_session();
		return curl_close($this->ch);
	}
	
	/**
		Destroi os cookies e a sessão
	*/
	function clean_session(){
		file_put_contents($this->cookiefile,'');
	}
	
	/**
		Loga no site e pega um cookie
	*/
	function login($endereco){
		// Concatena os campos do POST
		$postfields = $this->userfield."=".$this->user."&".$this->passfield."=".$this->pass;
		// Configura o cURL para logar
		curl_setopt ($this->ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt ($this->ch, CURLOPT_POST, 1);
		curl_setopt( $this->ch, CURLOPT_URL, $endereco );

		return curl_exec ($this->ch);
	}
	
	function post(){
		/*
		foreach($this->obter_produtos() as $produto){
			$produto = $this->separar_produto($produto);
			//configurar url e curl
		}
		*/
		$produto = '212995 Impressora laser color . C330DN# Emb: CX 1 UN CX 1 UN  C_Tabela: 950.211  Ult_Cust: 950.211  UltEnt: 26/12/2012 PVenda: 1349 Condição: 60 DDE                         MD: Sim';
		$produto = $this->separar_produto($produto);
		$postfields = array(
			'Loja' => '----Todas',
			'CPFJ' => '01619318',
			'Filial' => '0001',
			'Feiras' => '',
			'Produto' => $produto[1],
			'Linha2' => $produto[2],
			//'qtdProduto' => '36',
			/*'arrProd' => 
				'212995,212996,213009,213028,217708,217816,
				 218492,218529,220272,220273,220274,220279,
				 221482,221483,221493,225518,225519,225520,
				 225521,225526,225530,225536,225537,225538,
				 225539,225545,225550,225565,225566,225567,
				 225568,225570,225571,225572,225573,225574'*/
		);
		$postfields = http_build_query($postfields);
		
		// Configura o cURL para postar
		set_time_limit(0);
		curl_setopt ($this->ch, CURLOPT_CONNECTTIMEOUT ,0);
		curl_setopt ($this->ch, CURLOPT_TIMEOUT, 10000000000);
		curl_setopt ($this->ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt ($this->ch, CURLOPT_POST, 1);
		curl_setopt ($this->ch, CURLOPT_URL, 'http://b2b.kalunga.com.br/rvp/GravaSession.asp');
		return $this->limpar(curl_exec($this->ch));
	}
	
	private function separar_produto($produto){
		preg_match('/(.*?)\#(.*?$)/', $produto, $casamento);
		return $casamento;
	}
	
	private function obter_produtos(){
		curl_setopt( $this->ch, CURLOPT_URL, 'http://b2b.kalunga.com.br/rvp/produto.asp' );
		$result = curl_exec($this->ch);
		preg_match_all('/<option value=\'(.*?)\'[^>]*>(.*?)<\/option>\r\n/',strip_tags($result, "<option>"),$resultado);
		return $resultado[1];
	}
	
	function limpar($texto){	
		// Removendo Tags sem dados
		$result = preg_replace('/<(script|head|div|form)[^>]*>.*?<\/\1>/is','',$texto);
		$result = preg_replace('/<\/?(link|b|a|hr|br|img|font)[^>]*>/is','',$result);
		$result = preg_replace('/<(td) background = ".*?"[^>]*><\/\1>/is','',$result);
		// Removendo estilização
		$result = preg_replace('/(id|class|align|face|size|bgcolor|on\w*|colspan ?|cell\w*|width|height|border|color|style|background ?)= ?".*?"/is','',$result);
		// Removendo linhas vazias
		$result = preg_replace('/&nbsp;/is','',$result);
		$result = preg_replace('/<(td|tr) ?[^>]*>[\s\t]*<\/\1>/is','',$result);
		$result = preg_replace('/<(td|tr) ?[^>]*>[\s\t]*<\/\1>/is','',$result);
		$result = preg_replace('/<(td|tr) ?[^>]*>[\s\t]*<\/\1>/is','',$result);
		$result = preg_replace('/<(td|tr) ?[^>]*>[\s\t]*<\/\1>/is','',$result);
		// Removendo Comentários
		$result = preg_replace('/<!--(.*?)-->/is','',$result);
		// Reduzindo
		$result = preg_replace('/ +/is',' ',$result);
		return utf8_encode($result);
	}
	
	function analizar($input){
		$doc = new DOMDocument;
		$doc->loadHTML($input);
		$xpath = new DOMXPath($doc);
		$linhas = $xpath->query('//table[2]/tr[position()>3]');
		foreach($linhas as $linha){
			if($linha->hasChildNodes()){
				$filhos = $linha->childNodes;
				echo "[".($filhos->length)."] ";
				for($i=0;$i<20;$i++){
					$val = trim($filhos->item(2*$i)->nodeValue);
					if($i == 0 AND (int)$val < date('Y'))
						break 1;
					echo $val." ";
				}
				echo "\n";
			}
			echo "<br />";
		}
	}
}
error_reporting('E_PARSE');
$oki = new robot('config.ini');
// Login
//$result = $oki->login('http://b2b.kalunga.com.br/autentica.asp?act=1000');
//$oki->clean_session();
//$result = $oki->post();
//file_put_contents('page.html',$result);
//$oki->analizar($result);
$oki->analizar(file_get_contents('page.html'));
$oki->terminate();
?>