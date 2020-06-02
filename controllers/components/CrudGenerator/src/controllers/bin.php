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

class Bin extends Controller {

    public function init() {
        $this->load("controllers/components/CrudGenerator/src/models", "binModel");
    }

    public function crud($table_name, $bootstrap, $headerView = false, $footerView = false) {
        $echo = '<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">';
        $echo .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';
        $echo .= '<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>';
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/../controllers/{$table_name}.php")) {
            $echo .= "<center class=\"text-danger\">";
            $echo .= "<h2>Erro ao ciar CRUD da tabela {$table_name}!<br> O arquivo " . $_SERVER['DOCUMENT_ROOT'] . "/controllers/{$table_name}.php" . " já existe no servidor.</h2><br>";
            $echo .= "<a role='button' class='btn btn-xs btn-primary' href='/'>Voltar ao In&iacute;cio</a>";
            $echo .= "</center>";
        } else {
            $this->gerarCrud($table_name, $bootstrap, $headerView, $footerView);
            if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/../controllers/{$table_name}.php")) {
                $echo .= "<center class=\"text-success\">";
                $echo .= "<h2>Crud criado com sucesso para a tabela {$table_name}!</h2><br>";
                $echo .= "<h4>Para acessar o crud entre nas URLs abaixo: <br/><br/> <a href='/{$table_name}/listar'>http://endereco.exemplo/{$table_name}/listar</a> <br/> <a href='/{$table_name}/adicionar'>http://endereco.exemplo/{$table_name}/adicionar</a></h4>";
                $echo .= "<a role='button' class='btn btn-xs btn-primary' href='/'>Voltar ao In&iacute;cio</a>";
                $echo .= "</center>";
                $echo .= "<pre>";
                $table_structure = $this->binModel->getTableStructure($table_name);
                $echo .= print_r($table_structure, true);
                $echo .= "</pre>";
            } else {
                $echo .= "<center class=\"text-danger\">";
                $echo .= "<h2>Erro ao criar CRUD da tabela {$table_name}!<br> Verifique a permisa&atilde;o da pasta do OpenMvc.</h2><br>";
                $echo .= "<h4>De a permisa&atilde;o 0777 reucsivamente para a pasta {$_SERVER['DOCUMENT_ROOT']} e tente novamente.</h4>";
                $echo .= "<p>EX: sudo chmod -Rf 0777 {$_SERVER['DOCUMENT_ROOT']}</p>";
                $echo .= "<a role='button' class='btn btn-xs btn-primary' href='/'>Voltar ao In&iacute;cio</a>";
                $echo .= "</center>";
            }
        }
        echo parse_view_console($echo);
    }

    public function gerarCrud($table_name, $bootstrap = false, $headerView = false, $footerView = false) {
        $table_structure = $this->binModel->getTableStructure($table_name);
        $view_dir = "{$_SERVER['DOCUMENT_ROOT']}/../views/{$table_name}";
        $controller_dir = "{$_SERVER['DOCUMENT_ROOT']}/../controllers";
        $model_dir = "{$_SERVER['DOCUMENT_ROOT']}/../models";
        // CREATE VIEWS DIR
        mkdir($view_dir, 0777, true);
        touch($view_dir);
        chmod($view_dir, 0777);
        // CREATE VIEW list.php
        if (!file_exists($view_dir . '/list.php')) {
            $this->makeViewList($view_dir, $table_structure, $table_name, $bootstrap, $headerView, $footerView);
            touch($view_dir . '/list.php');
            chmod($view_dir . '/list.php', 0777);
        }
        // CREATE VIEW edit.php
        if (!file_exists($view_dir . '/edit.php')) {
            $this->makeViewEdit($view_dir, $table_structure, $bootstrap, $headerView, $footerView);
            touch($view_dir . '/edit.php');
            chmod($view_dir . '/edit.php', 0777);
        }
        // CREATE CONTROLLER {$table_name}.php
        if (!file_exists("$controller_dir/{$table_name}.php")) {
            $this->makeController($table_name);
            touch("$controller_dir/{$table_name}.php");
            chmod("$controller_dir/{$table_name}.php", 0777);
        }
        // CREATE MODEL {$table_name}.php
        if (!file_exists("$model_dir/{$table_name}Model.php")) {
            $this->makeModel($table_name);
            touch("$model_dir/{$table_name}Model.php");
            chmod("$model_dir/{$table_name}Model.php", 0777);
        }
    }

    public function makeModel($table_name) {
        $file_path = "{$_SERVER['DOCUMENT_ROOT']}/../models/{$table_name}Model.php";
        if (!file_exists($file_path)) {
            $RETURN_LISTAR = '$this->listar($page, $max_for_page)';
            $HAVE_FILEFIELD = '';
            $EXECUTE_FILEFIELD = FALSE;
            $table_structure = $this->binModel->getTableStructure($table_name);
            foreach ($table_structure as $keyStructure => $structure) {
                $inputType = $this->binModel->formType($structure->Type);
                if ($inputType != "file") {
                    $HAVE_FILEFIELD .= $structure->Field . ',';
                } else {
                    $EXECUTE_FILEFIELD = TRUE;
                }
            }
            if ($EXECUTE_FILEFIELD) {
                $RETURN_LISTAR = '$this->listar($page, $max_for_page, null, "' . substr($HAVE_FILEFIELD, 0, -1) . '")';
            }

            $fp = fopen($file_path, "wa");
            $php = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../controllers/components/CrudGenerator/src/bin/files/TABLE_Model.php");
            $php = str_replace("CLASS_NAME_TABLE_", ucwords($table_name), $php);
            $php = str_replace("TABLE_", $table_name, $php);
            $php = str_replace("/* RETURN_LISTAR */", $RETURN_LISTAR, $php);
            fwrite($fp, $php);
            fclose($fp);
            touch($file_path);
            chmod($file_path, 0777);
        } else {
            echo_error("O arquivo $file_path já existe! Abortando...", "CRUDGEN-01", false);
        }
    }

    public function makeController($table_name) {
        $file_path = "{$_SERVER['DOCUMENT_ROOT']}/../controllers/{$table_name}.php";
        if (PATH_SEPARATOR == ":") {
            $quebra = "\r\n";
        } else {
            $quebra = "\n";
        }
        if (!file_exists($file_path)) {
            $fp = fopen($file_path, "wa");
            $php = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../controllers/components/CrudGenerator/src/bin/files/TABLE_.php");
            $php = str_replace("CLASS_NAME_TABLE_", ucwords($table_name), $php);
            $php = str_replace("TABLE_", $table_name, $php);

            $LOAD_MODELS = "";
            $LIST_RELATIONS = "";
            $VAR_RELATIONS = "";
            $CONDITIONS = "";
            $OBJECTS = "";
            $FUNCTIONS = "";
            $HAVEFILEFIELD = FALSE;

            $DB_KEY = "Tables_in_" . DB_NAME;
            $tables = $this->binModel->getTables();
            $table_structure = $this->binModel->getTableStructure($table_name);
            foreach ($table_structure as $keyStructure => $structure) {
                foreach ($tables as $keyTable => $table) {
                    if (
                            ($structure->Field == "id_" . $table->$DB_KEY) ||
                            ($structure->Field == "ID_" . $table->$DB_KEY) ||
                            ($structure->Field == $table->$DB_KEY . "_id") ||
                            ($structure->Field == $table->$DB_KEY . "_ID")
                    ) {
                        $this->makeModel($table->$DB_KEY);
                        $LOAD_MODELS .= '        $this->load("models", "' . $table->$DB_KEY . 'Model");' . $quebra;
                        $LIST_RELATIONS .= '$' . $table->$DB_KEY . ' = $this->' . $table->$DB_KEY . 'Model->list_();' . $quebra;
                        $VAR_RELATIONS .= ', "' . $table->$DB_KEY . '" => $' . $table->$DB_KEY . '';
                    }
                }
                $inputType = $this->binModel->formType($structure->Type);
                if ($inputType == "checkbox") {
                    $CONDITIONS .= 'if(!empty($_POST["' . $structure->Field . '"]) && $_POST["' . $structure->Field . '"]){';
                    $CONDITIONS .= '$_POST["' . $structure->Field . '"] = 1;';
                    $CONDITIONS .= '}else{';
                    $CONDITIONS .= '$_POST["' . $structure->Field . '"] = 0;';
                    $CONDITIONS .= '}' . $quebra;
                }
                if ($inputType == "file") {
                    $HAVEFILEFIELD = TRUE;

                    $OBJECTS .= 'if (!empty($_FILES["' . $structure->Field . '"]["tmp_name"])){' . $quebra;
//                    $OBJECTS .= '                $obj->' . $structure->Field . ' = "data:".$_FILES["' . $structure->Field . '"]["type"].";".file_get_contents($_FILES["' . $structure->Field . '"]["tmp_name"]);' . $quebra;
                    $OBJECTS .= '                $obj->set' . ucfirst($structure->Field) . '("data:".$_FILES["' . $structure->Field . '"]["type"].";".file_get_contents($_FILES["' . $structure->Field . '"]["tmp_name"]));' . $quebra;
                    $OBJECTS .= '            }' . $quebra . '            ';
                }
            }
            IF ($HAVEFILEFIELD) {
                $FUNCTIONS .= 'public function download($params){' . $quebra;
                $FUNCTIONS .= '        $this->' . $table_name . 'Model->download($params);' . $quebra;
                $FUNCTIONS .= '     }' . $quebra;
            }
            $php = str_replace("/* LOAD_MODELS */", $LOAD_MODELS, $php);
            $php = str_replace("/* LIST_RELATIONS */", $LIST_RELATIONS, $php);
            $php = str_replace("/* VAR_RELATIONS */", $VAR_RELATIONS, $php);
            $php = str_replace("/* CONDITIONS */", $CONDITIONS, $php);
            $php = str_replace("/* OBJECTS */", $OBJECTS, $php);
            $php = str_replace("/* FUNCTIONS */", $FUNCTIONS, $php);

            fwrite($fp, $php);
            fclose($fp);
            touch($file_path);
            chmod($file_path, 0777);
        } else {
            echo_error("O arquivo $file_path já existe! Abortando...", "CRUDGEN-02", FALSE);
        }
    }

    public function makeViewList($view_dir, $table_structure, $table_name, $bootstrap = false, $headerView, $footerView) {
        if (PATH_SEPARATOR == ":") {
            $quebra = "\r\n";
        } else {
            $quebra = "\n";
        }
        $idStyle = 'id';
        $file_path = $view_dir . '/list.php';
        if (!file_exists($file_path)) {
            $fp = fopen($file_path, 'wa');
            $php = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../app/core/gnu.php") . $quebra;
            if ($bootstrap && empty($headerView)) {
                $php .= '<style>.openmvc-table {width:100%;}</style>' . $quebra;
                $php .= '<meta name="viewport" content="width=device-width, initial-scale=1">' . $quebra;
                $php .= '<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">' . $quebra;
                $php .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>' . $quebra;
                $php .= '<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>' . $quebra;
                $php .= '<script>function confirmDelete(el,url){el.classList.add(\'btn-warning\'); el.classList.remove(\'btn-danger\'); setTimeout(function(){ if(confirm(\'Deseja excluir este item?\')){ window.location=url;}else{el.classList.add(\'btn-danger\'); el.classList.remove(\'btn-warning\');} }, 1);}</script>' . $quebra;
            } else if (!empty($headerView)) {
                $php .= "{$headerView}" . $quebra;
            }
            $php .= '<div class="row">' . $quebra;
            $php .= '<div class="container">' . $quebra;
            $php .= '<div class="col-md-12 ">' . $quebra;
            $php .= '<a role="button" class="btn btn-primary" href="#" onclick="window.history.back();" ><span class="glyphicon glyphicon-circle-arrow-left"></span> Voltar</a> ' . $quebra;
            $php .= '<a role="button" class="btn btn-primary" href="/' . $table_name . '/adicionar" ><span class="glyphicon glyphicon-plus-sign"></span> Adicionar</a><br>' . $quebra;
            $php .= '<div class="table-responsive-xl ">' . $quebra;
            $php .= "<table class='openmvc-table table table-striped table-bordered table-hover'>" . $quebra
                    . "<thead>" . $quebra
                    . "<tr>" . $quebra;
            foreach ($table_structure as $key => $obj) {
                if ($obj->Field != "id" && $obj->Field != "ID") {
                    $php .= "<th>" . ucwords($obj->Field) . "</th>" . $quebra;
                } else {
                    $idStyle = $obj->Field;
                }
            }
            $php .= "<th>A&ccedil;&otilde;es</th>" . $quebra;
            $php .= "</tr>" . $quebra
                    . "</thead>" . $quebra
                    . "<tbody>" . $quebra
                    . '<?php foreach($list as $key => $obj): ?>' . $quebra
                    . '<tr>' . $quebra;
            foreach ($table_structure as $key => $obj) {
                if ($obj->Field != "id" && $obj->Field != "ID") {
                    $inputType = $this->binModel->formType($obj->Type);
                    if ($inputType != 'file')
                        $php .= '<td><?php echo $obj->get' . ucfirst($obj->Field) . '(); ?></td>' . $quebra;
                    else
                        $php .= '<td><a role="button" class="btn btn-xs btn-info" href="/' . $table_name . '/download/' . $obj->Field . '/<?php echo hash_id($obj->get' . ucfirst($idStyle) . '()); ?>"><span class="glyphicon glyphicon-cloud-download"></span> Baixar</a></td>' . $quebra;
                } else {
                    $fieldId = $obj->Field;
                }
            }
            $php .= '<td>'
                    . '<a  role="button" class="btn btn-xs btn-primary" href="/' . $table_name . '/editar/<?php echo hash_id($obj->get' . ucfirst($fieldId) . '()); ?>"><span class="glyphicon glyphicon-pencil"></span> Editar</a>&nbsp;'
                    . '<a role="button" class="btn btn-xs btn-danger" onclick="confirmDelete(this,\'/' . $table_name . '/deletar/<?php echo hash_id($obj->get' . ucfirst($fieldId) . '()); ?>\')" href="#"><span class="glyphicon glyphicon-trash"></span> Deletar</a>'
                    . '</td>' . $quebra;
            $php .= '</tr>' . $quebra
                    . '<?php endforeach; ?>' . $quebra;
            $php .= '</tbody>' . $quebra
                    . '</table>' . $quebra;
            $php .= '</div>' . $quebra;
            $php .= '</div>' . $quebra;
            $php .= '</div>' . $quebra;
            $php .= '</div>' . $quebra;
            if (!empty($footerView)) {
                $php .= "{$footerView}" . $quebra;
            }
            fwrite($fp, $php);
            fclose($fp);
            touch($file_path);
            chmod($file_path, 0777);
        } else {
            echo_error("O arquivo $file_path já existe! Abortando...", "CRUDGEN-02", FALSE);
        }
    }

    public function makeViewEdit($view_dir, $table_structure, $bootstrap = false, $headerView, $footerView) {
        if (PATH_SEPARATOR == ":") {
            $quebra = "\r\n";
        } else {
            $quebra = "\n";
        }
        $file_path = $view_dir . '/edit.php';
        if (!file_exists($file_path)) {
            $fp = fopen($file_path, 'wa');
            $DB_KEY = "Tables_in_" . DB_NAME;
            $tables = $this->binModel->getTables();
            foreach ($tables as $keyTable1 => $table1) {
                $mytables[] = "id_" . $table1->$DB_KEY;
                $mytables[] = "ID_" . $table1->$DB_KEY;
                $mytables[] = $table1->$DB_KEY . "_id";
                $mytables[] = $table1->$DB_KEY . "_ID";
            }
            $php = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/../app/core/gnu.php") . $quebra;
            if ($bootstrap && empty($headerView)) {
                $php .= '<style>.openmvc-form input, .openmvc-form textarea, .openmvc-form button, .openmvc-form select {width:100%;}</style>' . $quebra;
                $php .= '<meta name="viewport" content="width=device-width, initial-scale=1">' . $quebra;
                $php .= '<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">' . $quebra;
                $php .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>' . $quebra;
                $php .= '<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>' . $quebra;
            } else if (!empty($headerView)) {
                $php .= "{$headerView}" . $quebra;
            }
            $php .= '<div class="row">' . $quebra;
            $php .= '<div class="container">' . $quebra;
            $php .= '<div class="col-md-12 ">' . $quebra;
            $php .= '<a role="button" class="btn btn-primary" href="#" onclick="window.history.back();" ><span class="glyphicon glyphicon-circle-arrow-left"></span> Voltar</a> ' . $quebra;
            $php .= '<a role="button" class="btn btn-primary" href="/" ><span class="glyphicon glyphicon-home"></span> Home</a><br>' . $quebra;
            $php .= '<?php echo $forms->open(array("class"=>"openmvc-form"));?>' . $quebra;
//        print_r($table_structure);
            foreach ($table_structure as $key => $obj) {
                $escreveu = 0;
                if ($obj->Field != "id" && $obj->Field != "ID") {
                    foreach ($tables as $keyTable => $table) {
//                    echo $obj->Field . " => " . $table->$DB_KEY . "<br/>";
                        if ((
                                ($obj->Field == "id_" . $table->$DB_KEY) ||
                                ($obj->Field == "ID_" . $table->$DB_KEY) ||
                                ($obj->Field == $table->$DB_KEY . "_id") ||
                                ($obj->Field == $table->$DB_KEY . "_ID")) &&
                                !$escreveu
                        ) {
                            $relationTable_structure = $this->binModel->getTableStructure($table->$DB_KEY);
                            foreach ($relationTable_structure as $value) {
                                if ($value->Field == 'id' || $value->Field == 'ID') {
                                    $idTable = $value->Field;
                                }
                            }
                            //CREATE SELECT RELATION TABLES
                            $php .= '<div class="col-md-12 ">' . $quebra;
                            $php .= '<div class="form-group">' . $quebra;
                            $php .= '<?php' . $quebra
                                    . ' $data = array();' . $quebra
                                    . ' $data[] = array("label" => "--Selecione um ' . ucwords($obj->Field) . '--", "value" => "");' . $quebra
                                    . ' foreach($' . $table->$DB_KEY . ' as $key => $obj' . $table->$DB_KEY . '):' . $quebra
                                    . ' $data[] = array("label" => $obj' . $table->$DB_KEY . '->' . $idTable . ', "value" => $obj' . $table->$DB_KEY . '->' . $idTable . ');' . $quebra
                                    . ' endforeach;' . $quebra
                                    . ' $forms->fields["' . $obj->Field . '"] = new SelectField($data,' . ($obj->Null == "NO" ? "true" : "false") . ');' . $quebra
                                    . ' $forms->fields["' . $obj->Field . '"]->value = $obj->get' . ucfirst($obj->Field) . '();' . $quebra
                                    . ' echo $forms->label("' . $obj->Field . '", "' . ucwords($obj->Field) . '<br>");' . $quebra
                                    . ' echo $forms->render("' . $obj->Field . '",array("class"=>"form-control","placeholder"=>"' . ucwords($obj->Field) . '"));' . $quebra
                                    . '?>' . $quebra;
                            $php .= '</div>' . $quebra;
                            $php .= '</div>' . $quebra;
                            $escreveu = 1;
                        } else if (!$escreveu && !in_array($obj->Field, $mytables)) {
//                        echo $obj->Type." <br/>";
                            $inputType = $this->binModel->formType($obj->Type);
                            $loadClass = $this->binModel->formTypeHelpper($inputType);

                            $php .= '<div class="col-md-12 ">' . $quebra;
                            $php .= '<div class="form-group">' . $quebra;
                            $php .= '<?php' . $quebra
                                    . ' $forms->fields["' . $obj->Field . '"] = new ' . $loadClass . '(' . ($obj->Null == "NO" ? "true" : "false") . ');' . $quebra
                                    . ($inputType != "file" ? ' $forms->fields["' . $obj->Field . '"]->value = $obj->get' . ucfirst($obj->Field) . '();' . $quebra : '')
                                    . ' echo $forms->label("' . $obj->Field . '", "' . ucwords($obj->Field) . '<br>");' . $quebra
                                    . ' echo $forms->render("' . $obj->Field . '",array("class"=>"form-control","placeholder"=>"' . ucwords($obj->Field) . '"));' . $quebra
                                    . '?>' . $quebra;
                            $php .= '</div>' . $quebra;
                            $php .= '</div>' . $quebra;
                            $escreveu = 1;
                        }
                    }
                } else {
                    $php .= '<?php' . $quebra
                            . ' $forms->fields["' . $obj->Field . '"] = new HiddenField(false);' . $quebra
                            . ' $forms->fields["' . $obj->Field . '"]->value = hash_id($obj->get' . ucfirst($obj->Field) . '());' . $quebra
                            . ' echo $forms->render("' . $obj->Field . '");' . $quebra
                            . '?>' . $quebra;
                }
            }
            $php .= '<div class="col-md-6"><?php echo $forms->reset(\'<span class="glyphicon glyphicon-remove-circle"></span> Limpar\',array("class"=>"btn btn-danger","role"=>"button"));?></div>' . $quebra;
            $php .= '<div class="col-md-6"><?php echo $forms->submit(\'<span class="glyphicon glyphicon-ok-circle"></span> Enviar\',array("class"=>"btn btn-success","role"=>"button"));?></div>' . $quebra;
            $php .= '<?php echo $forms->close();?>' . $quebra;
            $php .= '</div>' . $quebra;
            $php .= '</div>' . $quebra;
            $php .= '</div>' . $quebra;
            if (!empty($footerView)) {
                $php .= "{$footerView}" . $quebra;
            }
            fwrite($fp, $php);
            fclose($fp);
            touch($file_path);
            chmod($file_path, 0777);
        } else {
            echo_error("O arquivo $file_path já existe! Abortando...", "CRUDGEN-03");
        }
    }

}

?>