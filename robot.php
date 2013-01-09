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
	private $csv;
	
	/**
		Inicializa um Robo a partir de um arquivo de configuracao
	*/
	function __construct($configfile){
		// Abrindo arquivo CSV
		$this->csv = fopen("kalunga_".date('Y_m_d-H_i_s').".csv",'a');
		//$this->csv = fopen("kalunga.csv",'a');
		$cabecalho = array('cod_produto','Ano','Loja','JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ','TOT','EST','MIN','MAX','Sugest','Saldo');
		fputcsv($this->csv, $cabecalho,";","\"");
		
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
		fclose($this->csv);
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
		$produtos = $this->obter_produtos();
		foreach($produtos as $produto){
			$produto = $this->separar_produto($produto);
		
			//$produto = '212995 Impressora laser color . C330DN# Emb: CX 1 UN CX 1 UN  C_Tabela: 950.211  Ult_Cust: 950.211  UltEnt: 26/12/2012 PVenda: 1349 Condição: 60 DDE                         MD: Sim';
			$produto = $this->separar_produto($produto);
			$produto_id = $produto[2];
			$postfields = array(
				'Loja' => '----Todas',
				'CPFJ' => '01619318',
				'Filial' => '0001',
				'Feiras' => '',
				'Produto' => $produto[1],
				'Linha2' => $produto[3],
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
			$result = $this->limpar(curl_exec($this->ch));
			$this->analizar($produto_id,$result);
		}
	}
	
	function separar_produto($produto){
		preg_match('/((\d+).*?)\#(.*?$)/', $produto, $casamento);
		return $casamento;
	}
	
	function obter_produtos(){
		curl_setopt( $this->ch, CURLOPT_URL, 'http://b2b.kalunga.com.br/rvp/produto.asp' );
		$result = curl_exec($this->ch);
		preg_match_all('/<option value=\'(.*?)\'[^>]*>(.*?)<\/option>\r\n/',strip_tags($result, "<option>"),$resultado);
		file_put_contents('option.html',$resultado[1]);
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
		return $result;
	}
	
	function analizar($produto_id,$input){
		// Carregar documento
		$doc = new DOMDocument;
		$doc->loadHTML($input);
		$xpath = new DOMXPath($doc);
		$linhas = $xpath->query('//table[2]/tr[position()>4]');
		$valoresTabela = array();
		
		// Leitura do documento
		foreach($linhas as $linha){
			if($linha->hasChildNodes()){
				
				$filhos = $linha->childNodes;
				// Capturando o conteudo da linha
				$valoresLinha = array();
				for($i=0;$i<($filhos->length/2);$i++){
					$valorCelula = trim($filhos->item(2*$i)->nodeValue);
					$valoresLinha[] = $valorCelula;
				}
				
				$valoresLinha = $this->filtrar($valoresLinha);
				if($valoresLinha !== NULL)
					array_unshift($valoresLinha,$produto_id);

				$valoresTabela[] = $valoresLinha;
			}
		}
		$this->salvar($valoresTabela);
	}
	
	function filtrar($linha){
		if(count($linha)<20)return null;
		//elseif((int)$linha[1] < date('Y'))return null;
		elseif(preg_match('/(geral|[CD. ]*(barueri|cliente ?ba\w*))/i',$linha[1])) return null;
		return $linha;
	}

	function salvar($tabela){
		foreach($tabela as $linha){
			fputcsv($this->csv, $linha,";","\"");
		}
	}
	
}
//error_reporting('E_PARSE');
$oki = new robot('config.ini');
// Login
//$result = $oki->login('http://b2b.kalunga.com.br/autentica.asp?act=1000');
//$oki->clean_session();
$result = $oki->post();
//print_r($oki->obter_produtos());
//file_put_contents('page.html',$result);
//$oki->analizar($result);
//$oki->analizar(212995,file_get_contents('page.html'));
$oki->terminate();
?>