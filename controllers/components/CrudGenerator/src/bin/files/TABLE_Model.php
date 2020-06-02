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
 * Model TABLE_Model.php criado por OpenMvc PHP Framework CrudGenerator
 */

class CLASS_NAME_TABLE_Model extends Model {

    var $name = "TABLE_";

    public function init() {
        $this->db->show_errors = FALSE;
    }

    public function delete_($id) {
        return $this->delete($id);
    }

    public function save_($dados) {
        return $this->save($dados);
    }

    public function list_($page = null, $max_for_page = null) {
        return /* RETURN_LISTAR */;
    }

    public function get_($id) {
        return $this->get($id);
    }

}

?>