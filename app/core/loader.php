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

class Loader {

    /**
     * Coleção de instâncias de objetos carregados armazenados estaticamente, evitando a duplicidade
     * de objetos em memória.
     * 
     * @var type 
     */
    private static $loadedItems = array();

    public function __construct() {
        // Configuracoes iniciais
    }

    /**
     * Carrega uma classe específica
     * 
     * @param String $item
     * @param String $name
     * @param String $params
     */
    public function load($item, $name, ...$params) {
        try {
            $parentClass = debug_backtrace()[1]['class'];
            $itemRegisterName = (!empty($parentClass) ? "{$parentClass}_{$item}" : $item);

            $loadedItem = $this->getLoadedItem($itemRegisterName, $name);
            if (null !== $loadedItem) {
                return $loadedItem;
            }

            $file = null;
            if ($item == "components")
                $file = "{$_SERVER['DOCUMENT_ROOT']}/../controllers/{$item}/{$name}/load.php";
            else
                $file = "{$_SERVER['DOCUMENT_ROOT']}/../{$item}/{$name}.php";

            if (empty($file) || !file_exists($file)) {
                $backtrace = debug_backtrace();
                echo_error("O arquivo <b>{$file}</b> n&atilde;o pode ser carregado!<br> Verifique se o arquivo existe e suas permiss&otilde;es.<p> <b>\$this->load(\"{$item}\",\"{$name}\")</b> em {$backtrace[0]['file']} na linha {$backtrace[0]['line']}</p>", 500);
                return false; // TODO: Lançar erro como exception???
            }
            require_once $file;

            $name = str_replace('/', '_', $name);
            $klass = ucfirst($name);

            if (!empty($params)) {
                $instance = new $klass(...$params);
            } else {
                $instance = new $klass();
            }

            $this->registerLoadedItem($itemRegisterName, $name, $instance);
        } catch (Exception $e) {
            echo_error("Exceção capturada: {$e->getMessage()}", 'Exception');
        }
    }

    private function registerLoadedItem($item, $name, $instance) {
        if (!isset(self::$loadedItems[$item]))
            self::$loadedItems[$item] = array();

        self::$loadedItems[$item][$name] = $instance;
        $this->$name = $instance;
    }

    protected function getLoadedItem($item, $name) {
        $collection = isset(self::$loadedItems[$item]) ? self::$loadedItems[$item] : null;
        $loadedItem = null;
        if (null !== $collection) {
            $loadedItem = isset($collection[$name]) ? $collection[$name] : null;

            if (null !== $loadedItem)
                if (!isset($this->$name))
                    $this->$name = $loadedItem;
        }


        return $loadedItem;
    }

    /**
     * Compara controller e action com a excecução atual
     * 
     * @param String $controller
     * @param String $action
     * $return Boolean
     */
    public function isControllerAction($controller, $action) {

        if ($controller == Mvc::getController() && $action == Mvc::getAction())
            return true;
        else
            return false;
    }

    /**
     * 
     * Registra no wordpress o caminho de um arquivo javascript
     * 
     * @param String $name
     * @param String $src - Optional
     */
    public function register_js($name, $src = null) {
        if (empty($src))
            $src = "/media/js/{$name}.js";

        //echo "<script type=\"text/javascript\" src=\"{$src}\"></script>";
        //wp_register_script($name, $src);
        $this->load_js($name);
    }

    /**
     * Carrega um js já registrado
     * 
     * @param String $name
     */
    public function load_js($name) {
        //wp_enqueue_script( $name );
    }

    /**
     * Vefiica se a url atual correponde
     * a url requisitada
     * @param String $url
     * @return Boolean
     */
    public function isUrl($url) {

        if (stristr($GLOBALS['PHP_SELF'], $url))
            return true;
        else
            return false;
    }

}
