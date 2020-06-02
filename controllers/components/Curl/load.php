<?php

/*
  Este arquivo é parte do OpenMvc PHP Framework

  OpenMvc PHP Framework é um software livre; você pode redistribuí-lo e/ou
  modificá-lo dentro dos termos da Licença Pública Geral GNU como
  publicada pela Fundação do Software Livre (FSF); na versão 2 da
  Licença, ou (na sua opinião) qualquer versão.

  Este programa é distribuído na esperança de que possa ser  útil,
  mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer
  MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a
  Licença Pública Geral GNU para maiores detalhes.

  Você deve ter recebido uma cópia da Licença Pública Geral GNU
  junto com este programa, se não, escreva para a Fundação do Software
  Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class Curl extends Controller {

    public function init() {
        
    }

    public function executeCurl($url, $post = array(), $method = "GET", $json_decode = true) {
        try {
            $handle = curl_init();

            curl_setopt($handle, CURLOPT_URL, $url);
            curl_setopt($handle, CURLOPT_HEADER, 0);

            if ($method !== 'GET') {
                if (!empty($post)) {
                    if ($method == 'POST') {
                        curl_setopt($handle, CURLOPT_POST, 1);
                    } elseif ($method == 'PUT') {
                        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "PUT");
                    } elseif ($method == 'PATCH') {
                        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "PATCH");
                    } elseif ($method == 'DELETE') {
                        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "DELETE");
                    }
                    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($post));
                }
                curl_setopt($handle, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($handle, CURLOPT_TIMEOUT, 10);
            }

            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);


            $return = curl_exec($handle);
            curl_close($handle);
            unset($handle);
            if ($json_decode) {
                return json_decode($return);
            } else {
                return ($return);
            }
        } catch (Exception $e) {
            to_log($e->getMessage(), "logs/mauticIntegrationsErrors.log");
            to_log($return, "logs/mauticIntegrationsErrors.log");
            to_log($this, "logs/mauticIntegrationsErrors.log");
            echo 'Exceção capturada: ', $e->getMessage(), "\n";
        }
    }

    public function execute($url, $post = array(), $method = "GET", $json_decode = true) {
        $this->executeCurl($url, $post, $method, $json_decode);
    }

}
