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

class Common extends Controller {

    public function header() {

        $this->view("common/header", array());
    }

    public function index() {
        $this->load_crud();
//        $this->view("common/index", array("var" => "Lorem Ipsum"));
    }

    public function footer() {

        $this->view("common/footer", array());
    }

    public function load_crud($params = array()) {
        $crud = (!empty($_REQUEST['crud']) ? $_REQUEST['crud'] : (!empty($params[2]) ? $params[2] : null));
        $bootstrap = (!empty($_REQUEST['bootstrap']) ? $_REQUEST['bootstrap'] : (!empty($params[3]) && $params[3] != "false" ? $params[3] : false));

        $this->load("components", "CrudGenerator");
        $this->CrudGenerator->headerView = '<?php execute_action("common", "header"); ?>';
        $this->CrudGenerator->footerView = '<?php execute_action("common", "footer"); ?>';
        $this->CrudGenerator->bootstrap = (bool) $bootstrap;
        $this->CrudGenerator->execute($crud);
    }

    public function crud($params = array()) {
        $this->lockActionToConsole("A action crud do CrudGenerator está travada para o acesso somente via console!");
        if (!empty($params)) {
            $action = $params[2]; // create | delete
            $table = $params[3];
            $bootstrap = (!empty($params[4]) ? $params[4] : false);
        }
        if ($action == "create") {
            $this->load_crud(array(null, null, $table, $bootstrap));
        }
        if ($action == "delete" || $action == "remove") {
            unlink("{$_SERVER["DOCUMENT_ROOT"]}/../controllers/{$table}.php");
            unlink("{$_SERVER["DOCUMENT_ROOT"]}/../models/{$table}Model.php");
            unlink("{$_SERVER["DOCUMENT_ROOT"]}/../models/persistences/{$table}Object.php");
            unlink("{$_SERVER["DOCUMENT_ROOT"]}/../views/{$table}/list.php");
            unlink("{$_SERVER["DOCUMENT_ROOT"]}/../views/{$table}/edit.php");
            rmdir("{$_SERVER["DOCUMENT_ROOT"]}/../views/{$table}/");
            echo parse_view_console("<h3>Crud {$table} removido com sucesso.</h3><br>");
        }
    }

}

?>