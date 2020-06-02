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
 * 
 *  ---GERADOR DE CRUD AUTOMÁTICO---
 * Controller TABLE_.php criado por OpenMvc PHP Framework CrudGenerator
 */

class CLASS_NAME_TABLE_ extends Controller {

    public function init() {
        $this->load("models", "TABLE_Model");
        /* LOAD_MODELS */
    }

    public function index() {
        $this->listar();
    }

    /* FUNCTIONS */

    public function pagina($param = array()) {
        $page = (int) (!isset($_REQUEST['page']) ? @$param[2] : @$_REQUEST['page']);
        $max_for_page = 100;
        $list = $this->TABLE_Model->list_($page, $max_for_page);
        $this->view("TABLE_/list", array("list" => $list));
    }

    public function listar($param = array()) {
        $list = $this->TABLE_Model->find();
        $this->view("TABLE_/list", array("list" => $list));
    }

    public function deletar($param) {
        $id = (!isset($_REQUEST['id']) ? @$param[2] : @$_REQUEST['id']);
        $id = (!empty($id) ? unhash_id($id) : null);

        $this->TABLE_Model->delete_($id);
        $this->redirect((!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "/TABLE_/listar"));
    }

    public function adicionar($param = array()) {
        $this->editar($param);
    }

    public function editar($param = array()) {
        Form::validateCsrf();
        $this->helpers[] = "forms";
        $id = (!isset($_REQUEST['id']) ? @$param[2] : @$_REQUEST['id']);
        $id = (!empty($id) ? unhash_id($id) : null);

        /* LIST_RELATIONS */
        if (empty($_POST)) {
            if (empty($id)) {
                $obj = $this->TABLE_Model->create();
            } else {
                $obj = $this->TABLE_Model->get_($id);
            }
        } else {
            /* CONDITIONS */
            (!empty($_POST['id'])) ? $_POST['id'] = unhash_id($_POST['id']) : null;
            $obj = $this->TABLE_Model->create($_POST);
            /* OBJECTS */
            $salvo = $this->TABLE_Model->save_($obj);
            if ($salvo) {
                $this->redirect("/TABLE_/listar");
            }
        }
        $this->view("TABLE_/edit", array("obj" => $obj /* VAR_RELATIONS */));
    }

}

?>