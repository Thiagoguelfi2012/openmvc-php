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
?>
<?php

class Controller extends Loader {

    var $helpers;
    var $name;
    var $action;
    var $layout;
    var $params = array();

    public function __construct() {
        $this->helpers = array();
        $this->init();
    }

    public function __destruct() {
        $this->destroy();
    }

    public function destroy() {
        
    }

    public function init() {
        
    }

    public function lockActionToConsole($msg = null) {
        global $openMVCRunFromConsole;
        if (!isset($openMVCRunFromConsole) || !$openMVCRunFromConsole) {
            if (empty($msg)) {
                $msg = "A action solicitada está travada para o acesso somente via console!";
            }
            echo_error($msg, 403, true);
        }
    }

    public function openmvc_observe_thread($params = []) {
        $this->lockActionToConsole();
        $controller = $params[3];
        $action = $params[4];
        $tmp = unserialize(base64_decode($params[5]));
        $tasks = $tmp['tasks'];
        $pid = $tmp['pid'];
        $this->load("components", "Threads");
        $this->Threads->setTasks($tasks);
        $out = $this->Threads->output($pid, 1);
        execute_action($controller, $action, $out[$pid]);
    }

    public function openmvc_run_thread($params = []) {
        $this->lockActionToConsole();
        $output_file = $params[2];
        $controller = $params[3];
        $action = $params[4];
        $parameters = (!empty($params[5]) ? unserialize(base64_decode($params[5])) : null);
        unset($params);
        $output = serialize(execute_action($controller, $action, $parameters));
        file_put_contents($output_file, $output);
        die;
    }

    /**
     * Adiciona um objeto no array parametros
     * @param mixed $data
     */
    public function addParams($data = array()) {

        $this->params = $data;


        if (!empty($_GET))
            $this->params = array_merge($this->params, $_GET);
    }

    /**
     * Este Método chama a view
     *  
     * @param String $name
     * @param Array $data
     * @params boolean $return Determina se o conteúdo da View deve ser retornado ou simplesmente direcionado à saída padrão (browser)
     */
    public function view($name, $data = array(), $return = false) {
        global $page_styles, $page_scripts;

        if (!empty($data))
            extract($data);

        $helpers = array();
        foreach ($this->helpers as $helper) {
            require_once("{$_SERVER['DOCUMENT_ROOT']}/../app/helpers/{$helper}.php");
            if (class_exists($klasse = ucfirst($helper))) {
                $helpers[$helper] = new $klasse();
            }
        }
        if (!empty($helpers))
            extract($helpers);

        ob_start();
        if (!empty($this->layout)) {
            ob_start();
            include "{$_SERVER['DOCUMENT_ROOT']}/../views/{$name}.php";
            $layout_content = ob_get_contents();
            ob_end_clean();
            include("{$_SERVER['DOCUMENT_ROOT']}/../views/layouts/{$this->layout}.php");
        } else {
            $viewPath = "{$_SERVER['DOCUMENT_ROOT']}/../views/{$name}.php";
            if (file_exists($viewPath)) {
                include($viewPath);
            }
        }

        $viewContents = ob_get_clean();
        $viewContents = parse_view_console($viewContents);

        if (true === $return)
            return $viewContents;
        else
            echo $viewContents;
    }

    /**
     * 	Redirecionamento basiado no framework cakePHP
     * 
     *     Como Usar:
     *     
     *        $url = array( "controller" => "nomedocontroller", "action" => "nomedaaction")
     *        
     *        $url = array("controller" => "nomedocontroller", "action" => "nomedaaction"), $params = array("param1","param2") 
     *        
     *        $url = "controller/action"
     *        
     *        $url = "action"   //executa o controller atual
     *        
     *        $url = "controller/action/param1/param2"
     *        
     *        $url = "controller/action" , $param = array("param1", "param2")
     *        
     * @Author Thiago Valentoni Guelfi
     * @Since 08/11/2010     
     * @param Mixed $url
     * @param Array $params
     */
    public function redirect($url, $params = array()) {
        echo parse_view_console("<meta http-equiv='refresh' content='0;url={$url}' >");
        die;
    }

    private function useSession() {
        if (!isset($_SESSION) || is_null($_SESSION)) {
            session_name('OpenMvc');
            session_start();
        }
    }

    protected function out_redirect($url) {
        echo "<meta http-equiv='refresh' content='0;url={$url}' >";
        die;
    }

    /**
     * Verifica se algum parametro é array
     * Esse método é usado para garantir que não seja passado um array 
     * como parametro na URL
     * @param Array $params
     * @return Boolean
     */
    private function hasArrayinArray($params) {
        foreach ($params as $param)
            if (is_array($param))
                return true;
        return false;
    }

    /**
     * Junta os parametros para serem passados na URL
     * @param Array $params
     * @return String
     */
    private function bindParams($params) {
        $bind = '';
        foreach ($params as $param)
            $bind = $bind . "/" . $param;
        return $bind;
    }

    /**
     * Recebe uma url com controller e action
     * retorna um arrray com o nome do controller e action
     * @param String $url
     * @return Array
     */
    private function getControllerActionFromUrl($url) {
        list( $controller, $action ) = explode("/", $url);
        return array($controller, $action);
    }

}
