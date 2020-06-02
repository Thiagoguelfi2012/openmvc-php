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

class BinModel extends Model {

    public function getTableStructure($table_name) {
        $return = $this->query("DESCRIBE {$table_name}");
        foreach ($return as &$re) {
            $re = (object) $re->internalObject();
        }
        return $return;
    }

    public function getTables() {
        $tables = $this->query("SHOW TABLES");
        foreach ($tables as &$table) {
            $table = (object) $table->internalObject();
        }
        return $tables;
    }

    public function formType($tipo_db) {
        $ultima = substr($tipo_db, -1);
        if ($ultima == ")") {
            $primeira = strrpos($tipo_db, '(');
            $tipo_db = substr($tipo_db, 0, $primeira);
        }
        $types = array(
            "default" => "text", // TIPO DE FORM DEFAULT
            "int" => "number",
            "tinyint" => "checkbox",
            "varchar" => "text",
            "text" => "textarea",
            "blob" => "file",
            "longblob" => "file",
            "tinyblob" => "file",
        );
        if (array_key_exists($tipo_db, $types)) {
            return $types[$tipo_db];
        } else {
            return $types["default"];
        }
    }

    public function formTypeHelpper($tipo_db) {
        $ultima = substr($tipo_db, -1);
        if ($ultima == ")") {
            $primeira = strrpos($tipo_db, '(');
            $tipo_db = substr($tipo_db, 0, $primeira);
        }
        $types = array(
            "text" => "CharField", // TIPO DE FORM DEFAULT
            "number" => "NumberField",
            "checkbox" => "CheckField",
            "textarea" => "TextField",
            "file" => "FileField",
        );
        if (array_key_exists($tipo_db, $types)) {
            return $types[$tipo_db];
        } else {
            return $types["default"];
        }
    }

}

?>