<?php

class WebCrawler{
	public $retry;
	private $threads;
	
	/**
		Adiciona uma thread na fila
		@param $url = Url da thread
		@param $opcoes = array de opções
	*/
	function adicionar_thread($url, $opcoes = false){
		$this->threads[] = curl_init($url);
		if($opcoes !== false){
			$num_thread =  count($this->threads) - 1;
			$this->mudar_opcoes($opcoes,$num_thread);
		}
	}
	
	/**
		Muda as opções de uma thread do Robo
		@param $opcoes = array com as opções e valores
		@param $num_thread = número da thread
	*/
	public function mudar_opcoes($opcoes, $num_thread = 0){
		curl_setopt_array($this->threads[$num_thread], $opcoes);
	}
	
	/**
		Muda uma opção de uma thread do Robo
		@param $opcao = nome da opção
		@param $valor = valor da opção
		@param $num_thread = número da thread
	*/
	public function mudar_opcao($opcao, $valor, $num_thread = 0){
		curl_setopt($this->threads[$num_thread], $opcao, $valor);
	}
	
	/**
		Retorna a informação de uma thread já executada
		@param $num_thread = número da thread a ser executada, se omitido retorna um array com a informação de todas as threads
		@param $opcao = opção a ser retornada, se omitido retorna todas as opções de informação
	*/
	public function info($num_thread = false, $opcao = false){
		if($num_thread === false){
			foreach($this->threads as $thread){
				if($opção === false){
					$informacao[] = curl_getinfo($this->threads[$num_thread]);
				}
				else{
					$informacao[] = curl_getinfo($this->threads[$num_thread],$opcao);
				}
			}
		}
		else{
			if($opção === false){
				$informacao[] = curl_getinfo($this->threads[$num_thread]);
			}
			else{
				$informacao[] = curl_getinfo($this->threads[$num_thread],$opcao);
			}
		}
		return $informacao;
	}
	
	/**
		Inicia a captura
		@param $num_thread = número da thread a ser executada
	*/
	public function executar($num_thread = false){
		$num_threads = count($threads);
		
		if($num_threads == 1){
			$result = executar_unica();
		}
		elseif($num_threads > 1){
			// Se não foi passado um valor, executa todas
			if($num_thread === false){
				$result = executar_todas();
			}
			// Se foi passado executa aquela em expecificamente
			else{
				$result = executar_unica($num_thread);
			}
		}
		
		if($result){
			return $result;
		}
	}
	
	/**
		Executa uma thread expecífica
		@param $num_thread = número da thread a ser executada, se não for expecificado executa a primeira
	*/
	public function executar_unica($num_thread = 0){
		if($this->retry > 0){
			$retry = $this->retry;
			do{
				$result = curl_exec($this->threads[$num_thread]);
				$http_status = curl_getinfo($result, CURLINFO_HTTP_CODE);
				$retry--;
			}while($retry >= 0 and ((int)$http_status != 200));
		}
		else{
			$result = curl_exec($this->threads[$num_thread]);
		}
		return $result;
	}
	
	/**
		Executa todas as threads na fila
	*/
	public function executar_todas(){
		
		// Adicionando todas as threads em um multi-handler
		$mh = curl_multi_init();
		foreach($this->threads as $num_thread => $thread){
			curl_multi_add_handle($mh,$thread);
		}
		
		 do{
			$mhc = curl_multi_exec($mh,$active);
		}while($mrc== CURLM_CALL_MULTI_PERFORM);
		
		while ($active && $mrc == CURLM_OK){
			if (curl_multi_select($mh) != -1){
				do{
					$mrc = curl_multi_exec($mh, $active);
				}while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		
		if ($mrc != CURLM_OK){
			echo "Curl multi read error $mrc\n";
		}
		
		foreach ($this->threads as $num_thread => $thread){
			$http_status = $this->info($num_thread, CURLINFO_HTTP_CODE);
			if($http_status[0] > 0 && $http_status[0] < 400){
				$resultados[] = curl_multi_getcontent($this->threads[$num_thread]);
			}
			else{
				if($this->retry > 0){
					$retry = $this->retry;
					$this->retry -= 1;
					$resultado = $this->executar_unica($num_thread);
					
					if($resultado){
						$resultados[] = $resultado;
					}
					else{
						$resultados[] = false;
					}
					$this->retry = $retry;
					echo '1';
				}
				else{
					$resultados[] = false;
				}
			}
			curl_multi_remove_handle($mh, $this->threads[$num_thread]);
		}
		curl_multi_close($mh);
		return $resultados;
	}
	
	/**
		Fecha um ou mais threads
		@param $num_thread = número da thread a ser fechada, se não informado fecha todas
	*/
	public function terminar($num_thread = false){
		if($num_thread === false){
			foreach($this->threads as $thread){
				curl_close($thread);
			}
		}
		else{
			curl_close($this->threads[$num_thread]);
		}
	}
	
	/**
		Retorna o erro de uma thread
		@param $num_thread = número da thread, se não informado retorna um array com todas threads
	*/
	public function erro($num_thread = false){
		if($num_thread === false){
			foreach($this->threads as $thread){
				$erros[] = curl_error($thread);
			}
		}
		else{
			$erros[] = curl_error($this->threads[$num_thread]);
		}
		return $erros;
	}
	
	/**
		Retorna número do erro de uma thread
		@param $num_thread = número da thread, se não informado retorna um array com todas threads
	*/
	public function erroNo($num_thread = false){
		if($num_thread === false){
			foreach($this->threads as $thread){
				$erros[] = curl_errno($thread);
			}
		}
		else{
			$erros[] = curl_errno($this->threads[$num_thread]);
		}
		return $erros;
	}
}
