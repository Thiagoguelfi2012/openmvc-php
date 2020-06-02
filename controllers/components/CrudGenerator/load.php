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

class CrudGenerator extends Controller {

    var $bootstrap = false;
    var $headerView = false;
    var $footerView = false;

    public function init() {
        $this->load("controllers/components/CrudGenerator/src/controllers", "bin");
        $this->load("controllers/components/CrudGenerator/src/models", "binModel");
    }

    public function execute($crud = null) {
        if (!empty($crud)) {
            $this->bin->crud($crud, $this->bootstrap, $this->headerView, $this->footerView);
        } else {
            $tables = $this->binModel->getTables();
            $this->view("../controllers/components/CrudGenerator/src/views/CrudGenerator", array("tables" => $tables));
        }
    }

}
