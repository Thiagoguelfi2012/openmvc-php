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

class Model extends Loader {

    /**
     *
     * @var $db
     */
    var $db;
    var $name; //ex. posts, terms, etc
    var $order;
    var $joins;
    var $tableDesc;

    public function __construct($db = null) {
        parent::__construct();
        if ($db == null) {
            global $db;
            $db = $db;
        }
        $this->db = $db;
        $this->init();
        if (!empty($this->name)) {
            $this->tableDesc = $this->db->get_results("DESCRIBE {$this->name}");
        }
    }

    public function __destruct() {
        @mysqli_close($this->db);
    }

    protected function init() {
        $this->order = "data";
    }

    public function query($sql) {
        $return = $this->db->get_results($sql);
        if (!empty($return)) {
            foreach ($return as &$row) {
                $row = $this->loadPersistence($row);
            }
        }
        return $return;
    }

    public function row($sql) {
        $row = $this->db->get_row($sql);
        if (!empty($row)) {
            return $this->loadPersistence($row);
        } else {
            return null;
        }
    }

    public function prepare($sql, $dados = array()) {
        return $this->db->prepare($sql, $dados);
    }

    public function get_row($sql) {
        return $this->loadPersistence($this->db->get_row($sql));
    }

    public function get_results($sql) {
        $return = $this->db->get_results($sql);
        if (!empty($return)) {
            foreach ($return as $key => &$value) {
                $value = $this->loadPersistence($value);
            }
        }
        return $return;
    }

    public function get_var($sql) {
        return $this->db->get_var($sql);
    }

    /**
     * Retorna o erro caso exista disparado pela última query.
     * 
     * @param null
     */
    public function last_error() {
        return $this->db->last_error;
    }

    /**
     * Retorna as linhas afetadas pela última query.
     * 
     * @param null
     */
    public function rows_affected() {
        return $this->db->rows_affected;
    }

    /**
     * Retorna o nome da Tabela do WordPress
     * Precisa sobrescrever a campo $name na subclasse da Model
     */
    public function getTableName($name = null) {
        if (empty($name))
            $name = $this->name;
        if (isset($this->db->$name))
            return $this->db->$name;
        else
            return $this->name;
    }

    public function printError() {
        echo $this->db->print_error();
    }

    protected function generateInsert($table, $data) {
        $fieldlist = array_keys($data);
        $fields = implode(",", $fieldlist);
        $valuelist = array();
        foreach ($data as $key => $value) {
            if ($value != NULL || $value === 0)
                $valuelist[] = $this->keyToSprintf($value);
            else {
                $valuelist[] = "NULL";
                unset($data[$key]);
            }
        }
        $values = implode(",", $valuelist);
        return $this->query(
                        $this->prepare("INSERT INTO {$table} ({$fields}) values ($values)", array_values($data))
        );
    }

    /**
     * Gera um UPDATE em uma tabela com base em um array WHERE.
     * 
     * @param array $data Dados para fazer UPDATE  ------- Ex: array('coluna' => 'valor')
     * @param array $where Dados para cláusula WHERE ----- Ex: array('coluna' => 'valor')
     * @param string $join Operador lógico do WHERE ------ Ex:(AND ou OR)
     * @param string $operator Operador matemático do WHERE -- Ex: (=, <=, >=, LIKE)  
     * @param string $table Nome da tabela (opcional) ----- Padrão $this->name
     */
    public function updateWhere($data, $where, $join = 'AND', $operator = '=', $table = null) {
        $fields = array();
        $values = array();
        if (empty($table))
            $table = $this->name;
        if (empty($where))
            $myWhere = $this->buildWhere($where, $join, true, $operator);
        if (!empty($data->internalObject())) {
            $data = $data->internalObject();
        }

        foreach ($data as $key => $value) {
            if ($key == "id" || $key == "ID")
                continue;

            if ($value === NULL || strtoupper($value) === 'NULL') {
                $fields[] = "{$key} = NULL";
            } else {
                $fields[] = "{$key} = " . $this->keyToSprintf($value);
                $values[] = $value;
            }
        }
        $field_and_value = implode(",", $fields);

        $sql = $this->prepare("UPDATE {$table} SET {$field_and_value} {$myWhere}", $values);
        return $this->query($sql);
    }

    protected function generateUpdate($table, $data, $id) {
        $fields = array();
        $values = array();
        foreach ($data as $key => $value) {
            if ($key == "id" || $key == "ID")
                continue;

            if ($value === NULL || strtoupper($value) === 'NULL') {
                $fields[] = "{$key} = NULL";
            } else {
                $fields[] = "{$key} = " . $this->keyToSprintf($value);
                $values[] = $value;
            }
        }
        $field_and_value = implode(",", $fields);
        $values[] = $id;

        $id_field = isset($data['ID']) ? 'ID' : 'id';
        $sql = $this->prepare("UPDATE {$table} SET {$field_and_value} WHERE {$id_field} = %d", $values);
        return $this->query($sql);
    }

    protected function keyToSprintf($value) {
        return "%s";
//        $floatVal = (float)($value);
//        return (is_numeric($value)) ? ((int)($floatVal) != $floatVal) ? "%f" : "%d" : "%s";
    }

    public function download($params, $return_image = false) {
        $tableName = $params[0];
        $fieldFile = $params[2];
        $fileId = unhash_id($params[3]);
        $obj = $this->row("SELECT $fieldFile FROM {$this->name} WHERE id = {$fileId}");
        $header = substr($obj->{"get" . ucwords($fieldFile)}(), 0, strpos($obj->{"get" . ucwords($fieldFile)}(), ';'));
        $mimeType = str_replace('data:', '', $header);
        $code_binary = str_replace("$header;", '', $obj->{"get" . ucwords($fieldFile)}());

        if (strstr($mimeType, "image")) {
//        header("Content-Type: image/jpeg");
//        echo $obj->{"get".ucwords($fieldFile)}();
//
            $cobe64 = base64_encode($code_binary);
            if ($return_image) {
                return "data:{$mimeType};base64,{$cobe64}";
            } else {
                echo "<img src='data:{$mimeType};base64,{$cobe64}' >";
            }
        } else {
            header('Content-Disposition: attachment; filename="' . $tableName . $fieldFile . $fileId . '.' . $this->mime_types_map(null, $mimeType) . '"');
            header('Content-Type: ' . $mimeType . ';');
            echo $code_binary;
        }
    }

    public function get_filename_ext($filename) {
        return strrev(substr(strrev($filename), 0, stripos(strrev($filename), '.')));
    }

    public function mime_types_map($ext = null, $mimeType = null) {
        $mime_types_map = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'sql' => 'application/sql',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'mp3' => 'audio/mp3',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        if (!empty($ext)) {
            return $mime_types_map[$ext];
        } else if (!empty($mimeType)) {
            $encontrou = false;
            foreach ($mime_types_map as $key => $value) {
                if ($value == $mimeType) {
                    if (!$encontrou)
                        return $key;
                    $encontrou = true;
                }
            }
        } else {
            return $mime_types_map;
        }
    }

    public function listar($pagina = null, $max_per_page = null, $status = null, $select_Fields = "*", $ORDER = "id") {
        $where = '';
        $limit = '';

        if (!empty($max_per_page)) {
            $pagina = (int) $pagina >= 1 ? (int) $pagina : 1;
            $offset = ($pagina - 1) * $max_per_page;

            $limit = "LIMIT {$max_per_page} OFFSET {$offset}";
        }

        if (!empty($status)) {
            $status = (int) $status;
            $where .= "WHERE status = {$status}";
        }

        $sql = "SELECT {$select_Fields} FROM {$this->name} {$where} ORDER BY {$ORDER} DESC {$limit}";

        return $this->query($sql);
    }

    public function get($id) {
        return $this->row($this->prepare("SELECT * FROM {$this->name} WHERE id = %d LIMIT 1", array($id)));
    }

    public function create($obj = null, $cleanObject = true) {
        return $this->loadPersistence($obj, $cleanObject);
    }

    public function loadPersistence($obj = [], $cleanObject = false) {
        $obj = (array) $obj;
        $internal = [];
        if ($cleanObject && !empty($this->tableDesc)) {
            foreach ($this->tableDesc as $intObj) {
                if (!array_key_exists($intObj->Field, $obj)) {
                    $internal[$intObj->Field] = null;
                } else {
                    $internal[$intObj->Field] = $obj[$intObj->Field];
                }
            }
        } else {
            $internal = $obj;
        }
        $className = ucfirst($this->name) . "Object";
        if ($className != "Object") {
            $persistenceFile = __DIR__ . "/../../models/persistences/{$this->name}Object.php";
            if (!file_exists($persistenceFile)) {
                $modelObject = str_replace("modelObject", $className, file_get_contents(__DIR__ . "/modelObject.php"));
                $modelObject = str_replace('**tableName**', $this->name, $modelObject);
                if (!is_dir(__DIR__ . "/../../models/persistences/")) {
                    mkdir(__DIR__ . "/../../models/persistences/");
                    touch(__DIR__ . "/../../models/persistences/");
                    chmod(__DIR__ . "/../../models/persistences/", 0777);
                }
                file_put_contents($persistenceFile, $modelObject);
                touch($persistenceFile);
                chmod($persistenceFile, 0777);
            }
            if (!class_exists($className)) {
                include_once $persistenceFile;
            }
            return new $className($internal);
        } else {
            return new modelObject($internal);
        }
    }

    public function save($dados) {
        return $this->salvar($dados);
    }

    public function salvar($dados) {
        $id = null;
        if (!empty($dados->internalObject())) {
            $dados = $dados->internalObject();
        }

        if (is_object($dados))
            $dados = (Array) $dados;

        if (isset($dados['id'])) {
            $id = !empty($dados['id']) ? $dados['id'] : null;
            unset($dados['id']);
        }

        if (isset($dados['ID'])) {
            $id = !empty($dados['ID']) ? $dados['ID'] : null;
            unset($dados['ID']);
        }

        if (null !== $id) {
            $this->generateUpdate($this->name, $dados, $id);
        } else {
            $this->generateInsert($this->name, $dados);
        }

        if (!empty($this->db->last_error)) {
            return false;
        } else {
            $id = !empty($this->db->insert_id) ? $this->db->insert_id : $id;
            return $id;
        }
    }

    public function count($params = array()) {
        $where = $this->buildWhere($params, 'AND', true);
        $sql = "SELECT count(0) as quantidade FROM {$this->name} t {$where}";
        return $this->row($sql)->quantidade;
    }

    /**
     * Cria join na tabela de acordo com os parametros recebidos.
     * 
     * @param array $params
     * @param string $table
     * @param string $joinType 
     * @param string $join - default AND
     * @param string $operator - default =
     */
    public function join($params = array(), $table, $joinType = '', $join = 'AND', $operator = '=') {
        $joins = $this->buildWhere($params, $join, FALSE, $operator);
        $joins = str_replace("'", " ", $joins);
        $this->joins[] = "{$joinType} JOIN {$table} ON ({$joins})";
        return $this;
    }

    /**
     * Pesquisa da tabela de acordo com os parametros recebidos.
     * 
     * @param array $params
     * @param array $fieĺds
     * @param string $join 
     * @param string $operator
     */
    public function find($params = array(), $fields = "*", $join = 'AND', $operator = '=', $order = "") {
        if (is_array(end($params))) {
            $join = "OR";
        }
        $where = $this->buildWhere($params, $join, true, $operator);
        $sql = "SELECT " . (is_array($fields) ? implode(", ", $fields) : $fields) . " FROM {$this->name} " . (!empty($this->joins) ? implode(" ", $this->joins) : "") . " {$where} {$order}";
        $this->joins = array();
        return $this->get_results($sql);
    }

    /**
     * Pesquisa recursivamente da tabela e seus relacionamentos de acordo com os parametros recebidos.
     * 
     * @param array $params
     * @param array $fieĺds
     * @param string $join 
     * @param string $operator
     * @param boolean $recursiveLoop
     */
    public function findAll($params = array(), $fields = "*", $join = 'AND', $operator = '=', $recursive = false) {
        if ($recursive) {
            if (empty($fields))
                $fields = "*";
            $where = $this->buildWhere($params, $join, true, $operator);
            $sql = "SELECT " . (is_array($fields) ? implode(", ", $fields) : $fields) . " FROM {$this->name} {$where}";
            $resultQuery = $this->get_results($sql);
            foreach ($resultQuery as $lineKey => $lineObj) {
                foreach ($lineObj as $colKey => $colObj) {
                    if (strstr($colKey, "_id") || strstr($colKey, "id_")) {
                        $tableName = str_replace("_id", "", str_replace("id_", "", $colKey));
                        $modelName = $tableName . "Model";
                        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/models/{$modelName}.php")) {
                            $this->load("models", "{$modelName}");
                            if ($this->$modelName->name == $tableName) {
                                $var = $this->$modelName->findAll(array("id" => $colObj), $fields, $join, $operator, $recursive);
                                $objectName = "{$this->name}.{$colKey}";
                                $resultQuery[$lineKey]->$objectName = $var[0];
                            }
                        }
                    }
                }
            }
        } else {
            $fields = substr($this->make_join_fields($this->name, $fields), 0, -1);
            $relation_join = $this->make_join($this->name);
            if (is_array(end($params))) {
                $join = "OR";
            }
            $where = $this->buildWhere($params, $join, true, $operator);
            $sql = "SELECT " . (is_array($fields) ? implode(", ", $fields) : $fields) . " FROM {$this->name} {$relation_join} {$where}";
            $resultQuery = $this->get_results($sql);
        }
        if (!$recursive) {
            foreach ($resultQuery as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $explode = explode("__OPENMVC__", $key2);
                    $return[$key]->$explode[0]->$explode[1] = $value2;
                }
            }
        } else {
            $return = $resultQuery;
        }
        return $return;
    }

    private function make_join_fields($table_name, $fields = array(), $have_relation_join = false) {
        if ($fields == "*")
            $fields = array();
        $describe = $this->query("DESCRIBE {$table_name}");
        $relation_join = "";
        foreach ($describe as $colKey => $colObj) {
            if ((in_array("{$table_name}.{$colObj->Field}", $fields) || empty($fields)) || (in_array("{$table_name}.*", $fields)))
                $relation_join .= " {$table_name}.{$colObj->Field} as {$table_name}__OPENMVC__{$colObj->Field},";
            $tableName = str_replace("_id", "", str_replace("id_", "", $colObj->Field));
            if (strstr($colObj->Field, "_id") || strstr($colObj->Field, "id_")) {
                $modelNameA = "{$_SERVER['DOCUMENT_ROOT']}/models/{$tableName}Model.php";
                $modelNameB = "{$_SERVER['DOCUMENT_ROOT']}/models/{$table_name}Model.php";
                if (file_exists($modelNameA) && file_exists($modelNameB)) {
                    $have_relation_join = true;
                    $relation_join .= $this->make_join_fields($tableName, $fields, $have_relation_join) . ",%virgula%";
                }
            }
        }
        if ($have_relation_join) {
            return str_replace("%virgula%", "", str_replace(",%virgula%", "", $relation_join));
        } else {
            return substr($relation_join, 0, -1);
        }
    }

    private function make_join($table_name) {
        $describe = $this->query("DESCRIBE {$table_name}");
        $relation_join = "";
        foreach ($describe as $colKey => $colObj) {
            if (strstr($colObj->Field, "_id") || strstr($colObj->Field, "id_")) {
                $tableName = str_replace("_id", "", str_replace("id_", "", $colObj->Field));
                $modelNameA = "{$_SERVER['DOCUMENT_ROOT']}/models/{$tableName}Model.php";
                $modelNameB = "{$_SERVER['DOCUMENT_ROOT']}/models/{$table_name}Model.php";
                if (file_exists($modelNameA) && file_exists($modelNameB)) {
                    $relation_join .= " LEFT JOIN {$tableName} ON ($tableName.id = {$table_name}.{$colObj->Field}) ";
                    if (!strstr($relation_join, "LEFT JOIN {$tableName} ON"))
                        $relation_join .= $this->make_join($tableName);
                }
            }
        }
        return $relation_join;
    }

    public function last() {
        return $this->row("SELECT * FROM {$this->name} ORDER BY ID DESC LIMIT 1");
    }

    /**
     * Deleta da tabela de acordo com os parametros.
     * 
     * @param int $id
     */
    public function deleteWhere($params) {
        $where = $this->buildWhere($params);
        $sql = "DELETE FROM  {$this->name} {$where}";
        return $this->db->query($sql);
    }

    /**
     * Deleta da tabela de acordo com o ID.
     * 
     * @param int $id
     */
    public function delete($id) {
        return $this->deletar($id);
    }

    /**
     * Deleta da tabela de acordo com o ID.
     * 
     * @param int $id
     */
    public function deletar($id) {
        $sql = $this->prepare("DELETE FROM  {$this->name} where id = %d", array($id));
        return $this->db->query($sql);
    }

    public function busca_palavra($field, $word) {
        return " lower({$field}) like \"%%{$word}%%\" ";
    }

    /**
     * Constroi uma clausua WHERE baseado nos parâmetros passados e na clausula de junção (AND ou OR).
     * 
     * @param array $params
     * @param string $join 
     * @param boolean $whereKeyword
     * @param string $operator
     */
    public function buildWhere($params = array(), $join = 'AND', $whereKeyword = true, $operator = '=') {
        $where = '';
        if (!empty($params)) {
            if (is_array($params)) {
                $_conditions = array();
                $lastKey = -1;
                foreach ($params as $key => $val) {
                    if (strtoupper($operator) == "LIKE") {
                        $_conditions[] = "`{$key}` LIKE '%{$val}%'";
                    } else if (strstr($key, " LIKE%%")) {
                        $_conditions[] = "`" . trim(str_replace("LIKE%%", "", $key)) . "` LIKE '%{$val}%'";
                    } else if ($val == NULL) {
                        if (substr(mb_strtoupper($key), -3) == "NOT") {
                            $_conditions[] = "`" . trim(substr($key, 0, -3)) . "` IS NOT NULL";
                        } else {
                            $_conditions[] = " `$key` IS NULL";
                        }
                    } else if (is_array($val) && !empty($val)) {
                        $joined_values = array();
                        $joined = false;
                        foreach ($val as $in_key => $in_val) {
                            $joined_values[] = is_numeric($in_val) ? $in_val : "'{$in_val}'";
                            $joined = true;

                            if (is_string($in_key)) {
                                if (!strstr($in_key, " LIKE%%")) {
                                    $joined2 = false;
                                    if (is_array($in_val)) {
                                        $joined_values_in = array();
                                        foreach ($in_val as $in_key2 => $in_val2) {
                                            if (is_numeric($in_val2)) {
                                                $_conditions[$key] = "(" . $this->buildWhere($val, "AND", false, $operator) . ")";
                                                $joined2 = true;
                                            }
                                        }
                                    } else {
                                        $_conditions[$key] = "(" . $this->buildWhere($val, "AND", false, $operator) . ")";
                                    }
                                } else {
                                    $_conditions[$key] = "(" . $this->buildWhere($val, "AND", false, $operator) . ")";
                                }
                            }
                        }
                        if ($joined) {
                            if (is_string($key)) {
                                $joined_valuesSTR = join(',', $joined_values);
                                $_conditions[] = "`{$key}` IN ({$joined_valuesSTR})";
                            }
                        }
                    } else {
                        $tmpKey = [$key, $operator];
                        if (strstr($key, " ")) {
                            $tmpKey = explode(" ", $key);
                        }
                        $_conditions[$key] = "`{$tmpKey[0]}` {$tmpKey[1]}" . (is_string($val) ? ($val == "NULL" ? $val : "'" . str_replace('"', "'", $val) . "'" ) : $val);
                        unset($tmpKey);
                    }
                }
                $join = strtoupper($join);
                $join = 'AND' == $join || 'OR' == $join ? " {$join} " : null;

                $prefix = $whereKeyword ? 'WHERE ' : '';

                $where = null !== $join ? $prefix . join($join, $_conditions) : '';
            } else {
                $where = (string) $params;
            }
        }
        return $where;
    }

    public function buildLimit($pagina, $max_per_page) {
        $limit = '';
        if (!empty($max_per_page)) {
            $max_per_page = (int) $max_per_page;
            $pagina = (int) $pagina >= 1 ? (int) $pagina : 1;
            $offset = ($pagina - 1) * $max_per_page;

            $limit = "LIMIT {$max_per_page} OFFSET {$offset}";
        }
        return $limit;
    }

}

/**
 * Implementaçã do padrão <em>Data Transfer Object</em> para fornecer, além de um valor absoluto 
 * para uma operação com a camada de modelagem, mensagens inteligíveis sobre a representação deste valor. 
 */
class ModelResult {

    private $value;
    private $messages = array();

    public function __construct() {
        $this->setValue(false);
    }

    /**
     * Define o valor da operação
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * Obtém  valor da operação
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Adiciona uma mensagem inteligível ao objeto de resultado
     * @param string $message 
     */
    public function addMessage($message) {
        $this->messages[] = $message;
    }

    /**
     *
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * @param string $glue Separador utilizado entre cas mensagens
     * @return string
     */
    public function getMessagesAsString($glue = '<br />') {
        return join($glue, $this->getMessages());
    }

    /**
     * Obtém a representação do objeto em formato array
     * @return array
     */
    public function toArray() {
        $result = array(
            'value' => $this->getValue(),
            'messages' => $this->getMessages()
        );

        return $result;
    }

    /**
     * Obtém a representação JSON do objeto de resultado
     * @return string
     */
    public function toJson() {
        return json_encode($this->toArray());
    }

}
