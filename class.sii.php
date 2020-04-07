<?php
require_once ('dom-crawler/Crawler.php');
date_default_timezone_set('America/Santiago');

use Symfony\Component\DomCrawler\Crawler; 

/**
 * Clase que permite consultar empresas en impuestos internos, devolviendo como resultado
 * un arreglo con: 
 *  1. razon social
 *  2. rut
 *  3. booleano indicando si es propyme o no
 *  4.fecha inicio de actividades
 *  5. arreglo con giros de la empresa (actividades)
 * 
 * @author Marcos Pulgar <marcos.pulgar.a@gmail.com>
 */

class Sii{
    
    function buscarEmpresaSII($rut){
		$url = 'https://zeus.sii.cl/cvc_cgi/stc/getstc';
        $respuesta = [
			'execution' => 1
		];
        
		try{
			$captcha = $this->fetch_captchat();
			$rut2 = explode("-", $rut );
			$data = [
				'RUT' => $rut2[0],
				'DV' => $rut2[1],
				'PRG' => 'STC',
				'OPC' => 'NOR',
				'txt_code' => $captcha['code'],
				'txt_captcha' => $captcha['captcha']
			];
			
			$data_f = http_build_query($data);
			
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data_f);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($curl);
            
            $respuesta['http_code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
			$respuesta['result'] = $this->formatearResultado($data, $rut);
		}catch(Exception $e){
			$respuesta['execution'] = 0;
			$respuesta['http_code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		}

		curl_close ($curl);

		return $respuesta;
    }
    
	private function formatearResultado($data, $rut){
		
		try{
			
			###################################################
			## se instancia Crawler, que recorrera el documento
			$craw = new Symfony\Component\DomCrawler\Crawler($data);

			################################################
			## se setean los path donde se encuentran los datos
			$XPATH_RAZON_SOCIAL = '//html/body/div/div[4]';
			$XPATH_RUT = '//html/body/div/div[6]';
			$XPATH_ACTIVITIES   = '//html/body/div/table[1]/tr';
			$XPATH_INICIO_ACTIVIDAD   = '//html/body/div/span[2]';
			$XPATH_FECHA_INICIO   = '//html/body/div/span[3]';
			$XPATH_PRO_PYME   = '//html/body/div/span[5]';

			$razonSocial = ucwords(strtolower(trim($craw->filterXPath($XPATH_RAZON_SOCIAL)->text())));
			$rut = ucwords(strtolower(trim($craw->filterXPath($XPATH_RUT)->text())));
			$proPyme = ucwords(strtolower(trim($craw->filterXPath($XPATH_PRO_PYME)->text())));
			$fc_actividad = ucwords(strtolower(trim($craw->filterXPath($XPATH_FECHA_INICIO)->text())));
			$fc_inicioActividad = substr($fc_actividad, 32);
			
			$gl_proPyme = substr($proPyme, 50);
			$bo_proPyme = 0;
			
			if(strtoupper(trim($gl_proPyme)) === "SI"){
				$bo_proPyme = 1;
			}
			
			$actividades = [];
			
			$craw->filterXPath($XPATH_ACTIVITIES)->each(function(Symfony\Component\DomCrawler\Crawler $node, $i) use (&$actividades){
				if($i > 0){
					$actividades[] = [
						'gl_giro' => $node->filterXPath('//td[1]/font')->text(),
						'gl_codigo' => $node->filterXPath('//td[2]/font')->text(),
						'gl_categoria' => $node->filterXPath('//td[3]/font')->text(),
						'gl_afecta' => $node->filterXPath('//td[4]/font')->text()					
					];
				}
			});

			$response['execution'] = 1;
            ######################################
			## se formatea la respuesta
			$response = [
				'arr_datosEmpresa' => [
                    'gl_razonSocial' => $razonSocial, 
					'gl_rut' => $rut,
					'bo_proPyme' => $bo_proPyme, 
                    'fc_inicioActividad' => $fc_inicioActividad
                ],
				'arr_actividades' => $actividades
			];
			
		}catch(Exception $e){
			$response['execution'] = 0;
		}
		
		return $response;
	}
	
	private function fetch_captchat(){
        $url = 'https://zeus.sii.cl/cvc_cgi/stc/CViewCaptcha.cgi';
        
        $data = ['oper' => '0'];
        
		$postvars = $this->formatData($data);
		
		$curl = curl_init($url);
		try{
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postvars);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2); 
			$request = curl_exec($curl);
			
			$code = substr($request, 75,128);
			
			$response = [
                'code' => substr(base64_decode($code),36,4),
				'captcha' => $code
			]; 
		}catch(Exception $e){
			$response['code_http'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		}
		
		return $response;
    }
    
    private function formatData($data){
        $args = '';
		foreach($data as $key=>$value) {
			$args .= $key . '=' . $value . '&';
        }
        return $args;
    }
}