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

class Field {

    var $value;
    var $required;
    var $error;

    /**
     *
     * @var boolean Define se labels devem ser 
     */
    protected $labelPosition = 'after';

    /**
     * Define o posicionamento do rótulo após o campo
     */
    const LABEL_POSITION_AFTER = 'after';

    /**
     * Posiciona o rótulo antes do campo
     */
    const LABEL_POSITION_BEFORE = 'before';
    const LABEL_POSITION_WRAP_BEFORE = 'wrap_before';
    const LABEL_POSITION_WRAP_AFTER = 'wrap_after';
    const LABEL_POSITION_NONE = 'none';

    /**
     * Atributos do campo
     */
    protected $attributes = array();

    public function __construct($required = true) {
        $this->required = $required;
        $this->error = false;
    }

    public function setValue($val) {
        $this->value = $val;
    }

    public function getValue($val) {
        $this->value = $val;
    }

    public function render($name, $attrs = array()) {
        $attrs = array_merge($this->attributes, $attrs);
        $attributes = $this->parseAttrs($attrs);
        return "<input {$attributes}/>";
    }

    public function setLabelPosition($position) {
        switch ($position) {
            case self::LABEL_POSITION_AFTER:
            case self::LABEL_POSITION_BEFORE:
            case self::LABEL_POSITION_WRAP_AFTER:
            case self::LABEL_POSITION_WRAP_BEFORE:
            case self::LABEL_POSITION_NONE:
                $this->itemLabelPosition = $position;
                break;
        }
    }

    protected function parseAttrs($attrs, $ignore_required = false) {
        if ($this->required && !$ignore_required) {
            $attrs['required'] = "required";
        }
        $output = array();
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                if (null !== $value) {
                    $output[] = sprintf('%s="%s"', $name, $value);
                }
            }
        }
        $attributes = join(' ', $output);
        return $attributes;
    }

    public function renderLabel($input, $id, $label, array $attrs = array()) {
        $attrs['for'] = $id;
        if ($this->error) {
            $class = 'field-with-error';
            if (isset($attrs['class'])) {
                $attrs['class'] .= ' ' . $class;
            } else {
                $attrs['class'] = $class;
            }
        }
        $attributes = $this->parseAttrs($attrs);

        $label_prefix = sprintf('<label %s>', $attributes);
        $label_suffix = '</label>';

        $tag = $input;
        switch ($this->labelPosition) {
            case self::LABEL_POSITION_AFTER:
                $tag = $input . $label_prefix . $label . $label_suffix;
                break;

            case self::LABEL_POSITION_BEFORE:
                $tag = $label_prefix . $label . $label_suffix . $input;
                break;

            case self::LABEL_POSITION_WRAP_AFTER:
                $tag = $label_prefix . $input . $label . $label_suffix;
                break;

            case self::LABEL_POSITION_WRAP_BEFORE:
                $tag = $label_prefix . $label . $input . $label_suffix;
                break;

            default:
            case self::LABEL_POSITION_NONE:
                $tag = $input;
                break;
        }

        return $tag;
    }

    /**
     * Verifica um dado índice/chave em um array, retorna o valor desta posição e a elimina. Um valor
     * padrão é obtido, caso a chave não esteja presente
     */
    protected function getAndUnset($key, array &$collection, $default = null) {
        $value = $default;
        if (isset($collection[$key])) {
            $value = $collection[$key];
            unset($collection[$key]);
        }
        return $value;
    }

}

class TextField extends Field {

    public function __construct($required = true) {
        parent::__construct($required);
    }

    public function render($name, $attrs = false) {
        $value = stripslashes($this->value);
        $attributes = array();
        $attributes['name'] = $attributes['id'] = $attributes['class'] = $name;

        if (is_array($attrs)) {
            $attributes = array_merge($attributes, $attrs);
        } else {
            if (true === $attrs) {
                // Retrocompatibilidade, o parâmetro determinava apenas o atributo 'disabled'
                $attributes['disabled'] = 'disabled';
            }
        }

        $attributes = $this->parseAttrs($attributes);

        return "<textarea {$attributes}>{$value}</textarea>";
    }

}

class InputField extends Field {

    var $type;

    public function __construct($required = true) {
        parent::__construct($required);
    }

    /**
     *
     * @param type $name
     * @param array $attrs
     * @param boolean $disable DEPRECATED
     * @return string
     */
    public function render($name, $attrs = array()) {
        $attributes = array(
            'name' => $name,
            'id' => $name,
            'value' => $this->value,
            'type' => $this->type
        );

        if (is_array($attrs)) {
            $attributes = array_merge($attributes, $attrs);
        } else {
            // Retrocompatibilidade; o parâmetro recebia uma string para determinar a classe CSS
            if (!empty($attrs)) {
                $attributes['class'] = $attrs;
            }

            if (true === $disable) {
                $attributes['disabled'] = 'disabled';
            }
        }

        return parent::render($name, $attributes);
    }

}

class NumberField extends InputField {

    public function __construct($required = true) {
        parent::__construct($required);
        $this->type = "number";
    }

}

class CharField extends InputField {

    public function __construct($required = true) {
        parent::__construct($required);
        $this->type = "text";
    }

}

class HiddenField extends InputField {

    public function __construct($required = false) {
        parent::__construct($required);
        $this->type = "hidden";
    }

}

class CheckField extends InputField {

    public function __construct($required = true) {
        parent::__construct($required);
        $this->type = "checkbox";
    }

    public function checked() {
        return ($this->value == "1") ? "checked=\"checked\"" : "";
    }

    public function render($name, $attrs = false) {
        $attributes = array(
            'type' => $this->type,
            'name' => $name,
            'id' => $name,
            'value' => 1
        );

        if ((int) $this->value == 1) {
            $attributes['checked'] = 'checked';
        }

        if (is_array($attrs)) {
            $attributes = array_merge($attributes, $attrs);
        } else {
            // Retrocompatibilidade; o parâmetro definia o atributo disabled
            if (true === $attrs) {
                $attributes['disabled'] = 'disabled';
            }
        }

        $parsedAttrs = $this->parseAttrs($attributes);

        return "<input {$parsedAttrs} />";
    }

}

class MultipleCheckField extends MultipleField {

    /**
     *
     * @var string Conteúdo a ser utilizado para prefixa um item checkbox
     */
    private $itemPrefix = '<div>';

    /**
     * @var string Sufixo para o item de checkbox
     */
    private $itemSuffix = '</div>';

    /**
     * Separador entre elementos <checkbox />
     * @var string 
     */
    private $itemSeparator = '<br />';

    public function __construct($data = array(), $required = true) {
        parent::__construct($data, $required);
    }

    public function setItemPrefix($content) {
        $this->itemPrefix = (string) $content;
    }

    public function setItemSuffix($content) {
        $this->itemSuffix = $content;
    }

    public function setItemSeparator($sep) {
        $this->itemSeparator = (string) $sep;
    }

    public function isChecked($item) {
        if (is_array($this->value))
            $values = array_values($this->value);
        else
            $values = array($this->value);

        return in_array($item['id'], $values);
    }

    public function checked($item) {
        return $this->isChecked($item) ? "checked=\"checked\"" : "";
    }

    public function item($name, $item, $contador, array $attrs = array()) {
        $id = "{$name}-{$contador}";
        $name = "{$name}[]";

        $prefix = $this->getAndUnset('item_prefix', $attrs, $this->itemPrefix);
        $suffix = $this->getAndUnset('item_suffix', $attrs, $this->itemSuffix);
        $this->itemSeparator = $this->getAndUnset('item_separator', $attrs, $this->itemSeparator);

        $attributes = $attrs + array(
            'name' => $name,
            'id' => $id,
            'type' => 'checkbox',
            'value' => $item['id']
        );

        if ($this->isChecked($item)) {
            $attributes['checked'] = 'checked';
        }

        $attributes = $this->parseAttrs($attributes);

        $input = "<input {$attributes}/>";

        return $prefix . $this->renderLabel($input, $id, $item['nome']) . $suffix;
    }

    public function get_separator($sep = null) {
        $sep = null === $sep ? $this->itemSeparator : (string) $sep;
        return parent::get_separator($sep);
    }

}

class FileField extends InputField {

    public function __construct($required = true) {
        parent::__construct($required);
        $this->type = "file";
    }

}

class MultipleField extends Field {

    var $data;

    public function __construct($data = array(), $required = true) {
        parent::__construct($required);
        $this->create($data);
    }

    public function create($data = array()) {
        $this->data = $data;
    }

    /**
     * Obtém a representação textual do elemento e, opcionalmente, seus atributos. Caso a chave 'pre_tag'
     * seja passada em $attrs, este valor será utilizado como atributos da tag que prefixa o conteúdo
     * dos múltiplos elementos.
     * 
     * @see MultipleField::pre_tag()
     * @param string $name
     * @param array $attrs
     * @return string
     */
    public function render($name, $attrs = array()) {
        $response = array();
        $counter = 0;
        $pre_tag_attrs = array();

        if (isset($attrs['pre_tag'])) {
            $pre_tag_attrs = $attrs['pre_tag'];
            unset($attrs['pre_tag']);
        }

        $sep = '';
        if (isset($attrs['separator'])) {
            $sep = $attrs['separator'];
            unset($attrs['separator']);
        }

        foreach ($this->data as $item) {
            if (is_object($item))
                $item = (array) $item;

            $response[] = $this->item($name, $item, $counter++, $attrs);
        }


        return $this->pre_tag($name, $pre_tag_attrs) . implode($this->get_separator($sep), $response) . $this->pos_tag($name);
    }

    public function pre_tag($name, $attrs) {
        return "";
    }

    /**
     * Obtém a string a ser usada como separador entre cada campo Multiplo. Para manter a compatibilidade
     * o método aceita um parâmetro a ser avaliado, caso esteja em branco o valor antigo (quebra de linha)
     * será usado.
     * 
     * @param string $sep
     */
    public function get_separator($sep = null) {
        return null === $sep ? '<br />' : (string) $sep;
    }

    public function pos_tag($name) {
        return "";
    }

    public function item($name, $item, $contador, array $attrs = array()) {
        throw new Exception("Não implementado");
    }

}

class RadioField extends MultipleField {

    protected $itemSeparator = '<br />';

    public function __construct($data = array(), $required = true) {
        parent::__construct($data, $required);
    }

    public function isChecked($item) {
        return ($this->value == $item['id']);
    }

    public function checked($item) {

        return $this->isChecked($item) ? "checked=\"checked\"" : "";
    }

    public function get_separator($sep = null) {
        return $this->itemSeparator;
    }

    public function item($name, $item, $contador, array $attrs = array()) {
        $id = $name . '_' . $contador;

        $this->itemSeparator = $this->getAndUnset('item_separator', $attrs, $this->itemSeparator);

        $prefix = $this->getAndUnset('item_prefix', $attrs, '');
        $suffix = $this->getAndUnset('item_suffix', $attrs, '');

        $attributes = $attrs + array(
            'type' => 'radio',
            'name' => $name,
            'id' => $id,
            'value' => $item['id']
        );

        if ($this->isChecked($item)) {
            $attributes['checked'] = 'checked';
        }

        $attributes = $this->parseAttrs($attributes);
        $tag = sprintf('<input %s>', $attributes);

        $this->setLabelPosition(self::LABEL_POSITION_AFTER);
        return $prefix . $this->renderLabel($tag, $id, $item['nome']) . $suffix;
    }

}

class SelectField extends MultipleField {

    private $groups = array();

    public function __construct($data = array(), $required = true) {
        parent::__construct($data, $required);
    }

    public function create($data = array()) {
        parent::create($this->parseGroups($data));
    }

    /**
     * Inverte a condição 'pre_tag' para que os atributos passados sejam adicionados à tag <select />
     * e não aos elementos <option />, como determina o comportamento padrão do método ancestral.
     * 
     * @see MultipleField::render()
     * @param string $name
     * @param array $attrs
     * @return string 
     */
    public function render($name, $attrs = array()) {
        $attributes = array();

        if (isset($attrs['options'])) {
            $attributes = $attrs['options'];
            unset($attrs['options']);
        }

        if (isset($attrs['pre_tag'])) {// pre_tag não é permitido para <select />, somente options no array de atributos
            unset($attrs['pre_tag']);
        }

        $attributes['pre_tag'] = $attrs;

        return parent::render($name, $attributes);
    }

    private function parseGroups(array $data, $group = null) {
        $items = array();

        $this->setGroup($group, 'open', reset($data));
        foreach ($data as $key => $item) {
            if (is_string($key)) {
                $items = array_merge($items, $this->parseGroups($item, $key));
            } else {
                $items[] = $item;
            }
        }
        $item = end($data);
        $this->setGroup($group, 'close', $item);

        return $items;
    }

    private function setGroup($group, $type, $item) {
        if (!is_null($group) && isset($item['value'])) {
            $id = $item['value'];
            $this->groups[$type][$id] = $group;
        }
    }

    public function item($name, $item, $contador, array $attrs = array()) {
        $pre_option_attrs = array();

        if (isset($attrs['pre_option'])) {
            $pre_option_attrs = $attrs['pre_option'];
            unset($attrs['pre_option']);
        }

        $attributes = $attrs + array(
            'value' => $item['value']
        );

        if ($this->isSelected($item)) {
            $attributes['selected'] = 'selected';
        }

        $tag = sprintf('<option %s>%s</option>', $this->parseAttrs($attributes, true), $item['label']);

        $prefix = $this->pre_option($item['value'], $pre_option_attrs);
        $suffix = $this->post_option($item['value']);

        return $prefix . $tag . $suffix;
    }

    private function pre_option($id, array $attrs = array()) {
        $pre_option = '';

        if (isset($this->groups['open'][$id])) {
            $label = $this->groups['open'][$id];

            $attributes = $attrs + array(
                'label' => $label
            );

            $pre_option = sprintf('<optgroup %s>', $this->parseAttrs($attributes, true));
        }

        return $pre_option;
    }

    private function post_option($id) {
        return (isset($this->groups['close'][$id])) ? '</optgroup>' : '';
    }

    /**
     *
     * @param type $name
     * @param array $cssclass
     * @param boolean $disable DEPRECATED
     * @return type 
     */
    public function pre_tag($name, $cssclass, $disable = false) {
        $attrs = array(
            'name' => $name,
            'value' => $name
        );

        if (is_array($cssclass)) {
            $attrs = $cssclass + $attrs;
        } else {
            if (!empty($cssclass)) {
                $attrs['class'] = $cssclass;
            }

            if ($disable) {
                $attrs['disabled'] = 'disabled';
            }
        }

        if (!empty($this->groups)) {
            $attrs['class'] = 'agrupado ' . (isset($attrs['class']) ? $attrs['class'] : '');
        }

        $attributes = $this->parseAttrs($attrs);

        return "<select {$attributes}>";
    }

    public function get_separator($sep = null) {
        return "";
    }

    public function pos_tag($name) {
        return "</select>";
    }

    public function isSelected($item) {
        return (((string) $this->value) === ((string) $item['value']));
    }

    public function checked($item) {
        return $this->isSelected($item) ? 'selected="selected"' : '';
    }

}

class Form extends Loader {

    /**
     *
     * @var array
     */
    public $fields;

    /**
     *
     * @var array
     */
    public $errors = array();

    /**
     *
     * @var array
     */
    public $messages = array();

    public function __construct($fields = array(), $data = array()) {
        $this->fields = $fields;
        if (is_object($data))
            $data = (Array) $data;

        $this->populate($data);
    }

    /**
     * Obtém uma instância de um campo de formulário, baseado em seu nome como referência
     * @param string $name
     * @return Field
     */
    public function field($name) {
        $field = isset($this->fields[$name]) ? $this->fields[$name] : null;
        return $field;
    }

    public function fill_field($field_name, $data = array()) {
        $field = $this->field($field_name);
        if (null !== $field) {
            if (method_exists($field, 'create')) {
                $field->create($data);
            }
        }
    }

    public function set_data($data = array()) {
        if (is_object($data))
            $data = (Array) $data;
        $this->populate($data);
    }

    public function valid() {
        $this->errors = array();
        foreach ($this->fields as $name => $field) {
            $field->error = false;
            if (!empty($field->required)) {
                if (empty($field->value) && $field->value !== "0") {
                    $this->errors[] = $name;
                    $field->error = true;
                    $this->messages[$name] = sprintf('O Campo %s é obrigatório', $name);
                }
            }
        }
        return count($this->errors) === 0;
    }

    /**
     *
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     *
     * @return string
     */
    public function getMessagesAsString($glue = '<br />') {
        return join($glue, $this->getMessages());
    }

    public function data() {
        $data = array();
        foreach ($this->fields as $name => $field) {
            $data[$name] = (empty($field) ? null : $field->value);
        }
        return $data;
    }

    /**
     * Getter e Setter para o valor de um campo especificado em $field_name. Caso $value seja omitida
     * o método atuará como getter. Caso contrário altera o valor para o especificado.
     * 
     * @param string $field_name
     * @param string $value
     * @return string 
     */
    public function value($field_name, $value = null) {
        $field = $this->field($field_name);
        if (null !== $value) {
            $field->value = $value;
        }
        return @$field->value;
    }

    public function render($field, $attrs = array()) {
        if (!is_array($attrs)) {
            // Retrocompatibilidade com o formato sem passagem de atributos
            $disable = $attrs;
            $attrs = array();
            if ((bool) $disable === true) {
                $attrs['disabled'] = 'disabled';
            }
        }
        return $this->fields[$field]->render($field, $attrs);
    }

    public function label($fieldName, $text = NULL, array $attrs = array()) {
        $field = isset($this->fields[$fieldName]) ? $this->fields[$fieldName] : null;
        $text = is_null($text) ? ucfirst($fieldName) : $text;

        if (null !== $field) {
            return $field->renderLabel('', $fieldName, $text, $attrs);
        }
        // Retrocompatibilidade com o formato antigo e fallback para campos inexistentes no form.
        return " <label for=\"{$fieldName}\" id=\"label-{$fieldName}\" {$this->contain_error($fieldName)}>{$text}</label>";
    }

    public static function newCsrf() {
        $token = md5(uniqid(session_id(), true)) . md5(uniqid(rand(0, time()), true));
        $_SESSION['OPENMVC_CSRF']["tokens"][] = $token;
        if (count($_SESSION['OPENMVC_CSRF']["tokens"]) > 100) {
            while (count($_SESSION['OPENMVC_CSRF']["tokens"]) > 100) {
                unset($_SESSION['OPENMVC_CSRF']["tokens"][0]);
                $_SESSION['OPENMVC_CSRF']["tokens"] = array_values($_SESSION['OPENMVC_CSRF']["tokens"]);
            }
        }
        return $token;
    }

    public static function validateCsrf() {
        if (!empty($_POST)) {
            $error = false;
            if (!isset($_POST['__openmvc_csrf']) || empty($_POST['__openmvc_csrf'])) {
                $error = 1;
            }
            if (!empty($_POST['__openmvc_csrf']) && !in_array($_POST['__openmvc_csrf'], $_SESSION['OPENMVC_CSRF']["tokens"])) {
                $error = 2;
            }
            if (!isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER'])) {
                $error = 3;
            }
            if (!isset($_SERVER['HTTP_ORIGIN']) || empty($_SERVER['HTTP_ORIGIN'])) {
                $error = 4;
            }
            if (!empty($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_ORIGIN']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_ORIGIN']) === false) {
                $error = 5;
            }
            if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_ORIGIN']) && strstr($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_HOST']) === false) {
                $error = 6;
            }
            if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
                $error = 7;
            }
            if (!empty($_POST['__openmvc_csrf']) && in_array($_POST['__openmvc_csrf'], $_SESSION['OPENMVC_CSRF']["tokens"])) {
                $key = array_search($_POST['__openmvc_csrf'], $_SESSION['OPENMVC_CSRF']["tokens"]);
                unset($_SESSION['OPENMVC_CSRF']["tokens"][$key]);
            }
            if ($error) {
                echo_error("Error on validate POST to OPENMVC! Error::{$error}", 500);
                return false;
            }
        }
    }

    private function inputCsrfToken() {
        $this->fields["__openmvc_csrf"] = new HiddenField(false);
        $this->fields["__openmvc_csrf"]->value = $this->newCsrf();
        return $this->render("__openmvc_csrf");
    }

    public function open($attrs = array()) {
        $opts = array("action" => "", "method" => "post", "enctype" => "multipart/form-data");
        $attributes = array_merge($opts, $attrs);
        $attr = $this->parseFormAttrs($attributes);
        return "<form {$attr}>" . $this->inputCsrfToken();
    }

    public function submit($text = "Enviar", $attrs = array()) {
        unset($attrs['type']);
        $attributes = $this->parseFormAttrs($attrs);
        return "<button {$attributes} type=\"submit\">{$text}</button>";
    }

    public function reset($text = "Limpar", $attrs = array()) {
        unset($attrs['type']);
        $attributes = $this->parseFormAttrs($attrs);
        return "<button {$attributes} type=\"reset\">{$text}</button>";
    }

    public function close() {
        return "</form>";
    }

    public function clean() {
        foreach ($this->fields as $name => $field) {
            $field->value = null;
        }
    }

    private function populate($data = array()) {
        if (!is_array($data)) {
            return;
        }

        foreach ($this->fields as $name => $field) {
            if (array_key_exists($name, $data)) {
                $field->value = $data[$name];
            }
        }
    }

    private function contain_error($field) {
        $has_field = isset($this->fields[$field]);
        return ($has_field && $this->fields[$field]->error) ? "class=\"field-with-error\"" : "";
    }

    protected function dados_multiplos($resultados, $id, $nome, $default = NULL) {
        $dados = array();
        if ($default != NULL) {
            $dados[] = $default;
        }

        foreach ($resultados as $dado) {
            if (is_array($dado))
                $dado = (Object) $dado;

            $dados[] = array("id" => $dado->$id, "nome" => $dado->$nome);
        }


        return $dados;
    }

    protected function parseFormAttrs($attrs) {
        $output = array();
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                if (null !== $value) {
                    $output[] = sprintf('%s="%s"', $name, $value);
                }
            }
        }
        $attributes = join(' ', $output);
        return $attributes;
    }

}
