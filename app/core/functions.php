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

@session_start();

include_once 'seed.class.php';
$seed = new Seed();

Class Files {

    public function readTemplate($file_name) {

        $file = fopen($file_name, "r");
        $read = fread($file, 100000);
        if (!$read)
            trigger_error("Erro inexperado ao tentar abrir o arquivo!", E_ERROR);
        return $read;
    }

    public function writeTemplate($file_name, $string) {

        $file = fopen($file_name, "w");
        $write = fwrite($file, $string, 100000);
        if (!$write)
            trigger_error("Erro inexperado ao tentar gravar o arquivo!", E_ERROR);

        return $write;
    }

}

/**
 * 	@Author Thiago Valentoni Guelfi
 * 	@Since 06-02-2010
 *
 * 	Imprime na tela o dump de um variavel php
 * 
 */
if (!function_exists('debug')) {

    function debug($var = false, $backTrace = false, $showHtml = false, $showFrom = true) {
        if ($showFrom) {
            $calledFrom = debug_backtrace();
            $time = date("H:i:s", time());
            echo '<strong>Debug =>' . substr($calledFrom[0]['file'], 1) . '</strong>';
            echo ' (Hora: <strong>' . $time . '</strong>) (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
        }
        echo "\n<pre>\n";

        $var = print_r($var, true);
        if ($showHtml) {
            $var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
        }

        if ($backTrace) {
            backtrace();
            echo "<h2>Dump:</h2>";
        }
        echo $var . "\n</pre>\n";
    }

}

if (!function_exists('backtrace')) {

    function backtrace() {
        $backtrace = array_reverse(debug_backtrace());
        echo "<h2>Backtrace:</h2>";
        $dados = "";
        foreach ($backtrace as $executado):
            $dados .= "<br><strong>Tempo:</strong> " . date("H:i:s");
            if (isset($executado["file"]))
                $dados .= "<br><strong>Arquivo:</strong><a href='file://{$executado["file"]}'>{$executado["file"]}</a>";
            if (isset($executado["line"]))
                $dados .= "  <strong>Linha:</strong> {$executado["line"]}";
            if (isset($executado["function"]))
                $dados .= "<br><strong>Metodo:</strong>  {$executado["function"]}";
            if (isset($executado["args"]))
                $dados .= "<br><strong>Parametros:</strong><br> <pre>" . print_r($executado["args"], true) .
                        "</pre><br>";
        endforeach;

        echo $dados;
    }

}

if (!function_exists('to_log')) {

    /**
     * @Author Thiago Valentoni Guelfi
     * @since  07-10-2010
     * 
     *  Geração de arquivo log basiado no metodo "$this->log()" 
     *  do CakePHP
     *   
     * @param Mixed $var
     */
    function to_log($var, $file_path = null) {
        $out = array();
        $calledFrom = debug_backtrace();
        $out[] = "Data:  " . date("F j, Y, g:i a");
        $out[] = 'Arquivo:' . substr($calledFrom[0]['file'], 1);
        $out[] = 'Linha: ' . $calledFrom[0]['line'];
        $out[] = "\n\n";
        $out[] = print_r($var, true);
        $out[] = "\n\n";

        $log_file = "logs/debug.log";
        if (null !== $file_path) {
            $log_file = $file_path;
        }

        if (!file_exists($log_file)) {
            $newLogFile = fopen($log_file, "w");
            touch($log_file);
            chmod($log_file, 0777);
        }

        file_put_contents($log_file, join("\n", $out), FILE_APPEND);
    }

}

if (!function_exists('date_parse_from_format')) {

    /**
     * POG para funcionar o strptime no Windows
     */
    function date_parse_from_format($format, $date) {
        $returnArray = array('hour' => 0, 'minute' => 0, 'second' => 0,
            'month' => 0, 'day' => 0, 'year' => 0);

        $dateArray = array();

        // array of valid date codes with keys for the return array as the values
        $validDateTimeCode = array('Y' => 'year', 'y' => 'year',
            'm' => 'month', 'n' => 'month',
            'd' => 'day', 'j' => 'day',
            'H' => 'hour', 'G' => 'hour',
            'i' => 'minute', 's' => 'second');

        /* create an array of valid keys for the return array
         * in the order that they appear in $format
         */
        for ($i = 0; $i <= strlen($format) - 1; $i++) {
            $char = substr($format, $i, 1);

            if (array_key_exists($char, $validDateTimeCode)) {
                $dateArray[$validDateTimeCode[$char]] = '';
            }
        }

        // create array of reg ex things for each date part
        $regExArray = array('.' => '\.', // escape the period
            // parse d first so we dont mangle the reg ex
            // day
            'd' => '(\d{2})',
            // year
            'Y' => '(\d{4})',
            'y' => '(\d{2})',
            // month
            'm' => '(\d{2})',
            'n' => '(\d{1,2})',
            // day
            'j' => '(\d{1,2})',
            // hour
            'H' => '(\d{2})',
            'G' => '(\d{1,2})',
            // minutes
            'i' => '(\d{2})',
            // seconds
            's' => '(\d{2})');

        // create a full reg ex string to parse the date with
        $regEx = str_replace(array_keys($regExArray), array_values($regExArray), $format);

        // Parse the date
        preg_match("#$regEx#", $date, $matches);

        // some checks...
        if (!is_array($matches) ||
                $matches[0] != $date ||
                sizeof($dateArray) != (sizeof($matches) - 1)) {
            return $returnArray;
        }

        // an iterator for the $matches array
        $i = 1;

        foreach ($dateArray AS $key => $value) {
            $dateArray[$key] = $matches[$i++];

            if (array_key_exists($key, $returnArray)) {
                $returnArray[$key] = $dateArray[$key];
            }
        }

        return $returnArray;
    }

}

if (!function_exists('isAndroid')) {

    function isAndroid() {

        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match("/Android/", $agent) != false)
            $android = true;
        else
            $android = false;

        return $android;
    }

}

if (!function_exists('url_atual')) {

    function url_atual($show_uri = true) {
        $pageURL = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        if ($show_uri) {
            $show = "REQUEST_URI";
        } else {
            $show = "QUERY_STRING";
        }
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER[$show];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER[$show];
        }
        return $pageURL;
    }

}

if (!function_exists('pogtime')) {

    function pogtime($date, $format) {
        $dateArray = array();

        $dateArray = date_parse_from_format($format, $date);

        if (is_array($dateArray)) {
            return mktime($dateArray['hour'], $dateArray['minute'], $dateArray['second'], $dateArray['month'], $dateArray['day'], $dateArray['year']);
        } else {
            return 0;
        }
    }

}

if (!function_exists('encrypt')) {

    function encrypt($string, $seed, $cipher = "AES-128-CBC") {
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($string, $cipher, $seed, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $seed, $as_binary = true);
        return base64_encode($iv . $hmac . $ciphertext_raw);
    }

}

if (!function_exists('decrypt')) {

    function decrypt($string, $seed, $cipher = "AES-128-CBC") {
        $c = base64_decode($string);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        return openssl_decrypt($ciphertext_raw, $cipher, $seed, $options = OPENSSL_RAW_DATA, $iv);
    }

}

if (!function_exists('mysql2date')) {

    /**
     * Converts MySQL DATETIME field to user specified date format.
     *
     * If $dateformatstring has 'G' value, then gmmktime() function will be used to
     * make the time. If $dateformatstring is set to 'U', then mktime() function
     * will be used to make the time.
     *
     * The $translate will only be used, if it is set to true and it is by default
     * and if the $wp_locale object has the month and weekday set.
     *
     * @since 0.71
     *
     * @param string $dateformatstring Either 'G', 'U', or php date format.
     * @param string $mysqlstring Time from mysql DATETIME field.
     * @param bool $translate Optional. Default is true. Will switch format to locale.
     * @return string Date formated by $dateformatstring or locale (if available).
     */
    function mysql2date($dateformatstring, $mysqlstring, $translate = true) {
        $m = $mysqlstring;
        if (empty($m))
            return false;

        if ('G' == $dateformatstring) {
            return strtotime($m . ' +0000');
        }

        $i = strtotime($m);

        if ('U' == $dateformatstring)
            return $i;

        if (true === $translate) {
            if (trim($dateformatstring) == 'd/M') {

                $meses = array('01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', '05' => 'Mai', '06' => 'Jun',
                    '07' => 'Jul', '08' => 'Ago', '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez');
                return date('d', $i) . "/" . $meses[date('m', $i)];
            }
            return date_i18n($dateformatstring, $i);
        } else if ('strftime' === $translate) {
            setlocale(LC_TIME, 'pt_BR');
            return strftime($dateformatstring, $i);
        } else {
            return date($dateformatstring, $i);
        }
    }

}

if (!function_exists('size_format')) {

    /**
     * Convert number of bytes largest unit bytes will fit into.
     *
     * It is easier to read 1kB than 1024 bytes and 1MB than 1048576 bytes. Converts
     * number of bytes to human readable number by taking the number of that unit
     * that the bytes will go into it. Supports TB value.
     *
     * Please note that integers in PHP are limited to 32 bits, unless they are on
     * 64 bit architecture, then they have 64 bit size. If you need to place the
     * larger size then what PHP integer type will hold, then use a string. It will
     * be converted to a double, which should always have 64 bit length.
     *
     * Technically the correct unit names for powers of 1024 are KiB, MiB etc.
     * @link http://en.wikipedia.org/wiki/Byte
     *
     * @since 2.3.0
     *
     * @param int|string $bytes Number of bytes. Note max integer size for integers.
     * @param int $decimals Precision of number of decimal places. Deprecated.
     * @return bool|string False on failure. Number string on success.
     */
    function size_format($bytes, $decimals = 0) {
        $quant = array(
            // ========================= Origin ====
            'TB' => 1099511627776, // pow( 1024, 4)
            'GB' => 1073741824, // pow( 1024, 3)
            'MB' => 1048576, // pow( 1024, 2)
            'kB' => 1024, // pow( 1024, 1)
            'B ' => 1, // pow( 1024, 0)
        );
        foreach ($quant as $unit => $mag)
            if (doubleval($bytes) >= $mag)
                return number_format_i18n($bytes / $mag, $decimals) . ' ' . $unit;

        return false;
    }

}

if (!function_exists('maybe_unserialize')) {

    /**
     * Unserialize value only if it was serialized.
     *
     * @since 2.0.0
     *
     * @param string $original Maybe unserialized original, if is needed.
     * @return mixed Unserialized data can be any type.
     */
    function maybe_unserialize($original) {
        if (is_serialized($original)) // don't attempt to unserialize data that wasn't serialized going in
            return @unserialize($original);
        return $original;
    }

}

if (!function_exists('is_serialized')) {

    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @since 2.0.5
     *
     * @param mixed $data Value to check to see if was serialized.
     * @return bool False if not serialized and true if it was.
     */
    function is_serialized($data) {
        // if it isn't a string, it isn't serialized
        if (!is_string($data))
            return false;
        $data = trim($data);
        if ('N;' == $data)
            return true;
        if (!preg_match('/^([adObis]):/', $data, $badions))
            return false;
        switch ($badions[1]) {
            case 'a' :
            case 'O' :
            case 's' :
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                    return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                    return true;
                break;
        }
        return false;
    }

}

if (!function_exists('is_serialized_string')) {

    /**
     * Check whether serialized data is of string type.
     *
     * @since 2.0.5
     *
     * @param mixed $data Serialized data
     * @return bool False if not a serialized string, true if it is.
     */
    function is_serialized_string($data) {
        // if it isn't a string, it isn't a serialized string
        if (!is_string($data))
            return false;
        $data = trim($data);
        if (preg_match('/^s:[0-9]+:.*;$/s', $data)) // this should fetch all serialized strings
            return true;
        return false;
    }

}

if (!function_exists('maybe_serialize')) {

    /**
     * Serialize data, if needed.
     *
     * @since 2.0.5
     *
     * @param mixed $data Data that might be serialized.
     * @return mixed A scalar data
     */
    function maybe_serialize($data) {
        if (is_array($data) || is_object($data))
            return serialize($data);

        if (is_serialized($data))
            return serialize($data);

        return $data;
    }

}

if (!function_exists('build_query')) {

    /**
     * Build URL query based on an associative and, or indexed array.
     *
     * This is a convenient function for easily building url queries. It sets the
     * separator to '&' and uses _http_build_query() function.
     *
     * @see _http_build_query() Used to build the query
     * @link http://us2.php.net/manual/en/function.http-build-query.php more on what
     * 		http_build_query() does.
     *
     * @since 2.3.0
     *
     * @param array $data URL-encode key/value pairs.
     * @return string URL encoded string
     */
    function build_query($data) {
        return _http_build_query($data, null, '&', '', false);
    }

}

if (!function_exists('add_magic_quotes')) {

    /**
     * Walks the array while sanitizing the contents.
     *
     * @since 0.71
     *
     * @param array $array Array to used to walk while sanitizing contents.
     * @return array Sanitized $array.
     */
    function add_magic_quotes($array) {
        foreach ((array) $array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = add_magic_quotes($v);
            } else {
                $array[$k] = addslashes($v);
            }
        }
        return $array;
    }

}

if (!function_exists('get_status_header_desc')) {

    /**
     * Retrieve the description for the HTTP status.
     *
     * @since 2.3.0
     *
     * @param int $code HTTP status code.
     * @return string Empty string if not found, or description if found.
     */
    function get_status_header_desc($code) {
        $code = abs(intval($code));

        $header_to_desc = [
            100 => "Continue",
            101 => "Switching Protocol",
            102 => "Processing",
            103 => "Early Hints",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            207 => "Multi-Status",
            208 => "Multi-Status",
            226 => "IM Used",
            300 => "Multiple Choice",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            307 => "Temporary Redirect",
            308 => "Permanent Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Timeout",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Payload Too Large",
            414 => "URI Too Long",
            415 => "Unsupported Media Type",
            416 => "Requested Range Not Satisfiable",
            417 => "Expectation Failed",
            418 => "I'm a teapot",
            421 => "Misdirected Request",
            422 => "Unprocessable Entity",
            423 => "Locked",
            424 => "Failed Dependency",
            425 => "Too Early",
            426 => "Upgrade Required",
            428 => "Precondition Required",
            429 => "Too Many Requests",
            431 => "Request Header Fields Too Large",
            451 => "Unavailable For Legal Reasons",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Timeout",
            505 => "HTTP Version Not Supported",
            506 => "Variant Also Negotiates",
            507 => "Insufficient Storage",
            508 => "Loop Detected",
            510 => "Not Extended",
            511 => "Network Authentication Required"
        ];

        if (isset($header_to_desc[$code]))
            return $header_to_desc[$code];
        else
            return '';
    }

}

if (!function_exists('cache_javascript_headers')) {

    /**
     * Set the headers for caching for 10 days with JavaScript content type.
     *
     * @since 2.1.0
     */
    function cache_javascript_headers($expiresOffset = 864000) {
        header("Content-Type: text/javascript; charset=UTF-8");
        header("Vary: Accept-Encoding"); // Handle proxies
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expiresOffset) . " GMT");
    }

}

if (!function_exists('bool_from_yn')) {

    /**
     * Whether input is yes or no. Must be 'y' to be true.
     *
     * @since 1.0.0
     *
     * @param string $yn Character string containing either 'y' or 'n'
     * @return bool True if yes, false on anything else
     */
    function bool_from_yn($yn) {
        return ( strtolower($yn) == 'y' );
    }

}

if (!function_exists('is_ssl')) {

    /**
     * Determine if SSL is used.
     *
     * @since 2.6.0
     *
     * @return bool True if SSL, false if not used.
     */
    function is_ssl() {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS']))
                return true;
            if ('1' == $_SERVER['HTTPS'])
                return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
            return true;
        }
        return false;
    }

}

if (!function_exists('execute_action')) {

    function execute_action($controller, $action, $params = null) {
        require_once("{$_SERVER['DOCUMENT_ROOT']}/../app/core/functions.php");
        $controller_path = "/controllers/{$controller}.php";
        if (is_file("{$_SERVER['DOCUMENT_ROOT']}/.." . $controller_path)) {
            try {
                include_once ("{$_SERVER['DOCUMENT_ROOT']}/.." . $controller_path);
                $klass = ucfirst($controller);
                $instance = new $klass($controller, $action);
                if (method_exists($instance, $action)) {
                    if (empty($params))
                        return $instance->$action();
                    else
                        return $instance->$action($params);
                } else {
                    $backtrace = debug_backtrace();
//            pr($backtrace[0][line]);
                    echo_error("A action <b>{$action}()</b> n&atilde;o foi encontrada no arquivo <b>$controller_path</b>!<br> Verifique o controller.<p><b>execute_action(\"{$controller}\",\"{$action}\")</b> em {$backtrace[0]['file']} na linha {$backtrace[0]['line']}</p>", 500);
                }
            } catch (Exception $e) {
                echo_error("Exceção capturada: {$e->getMessage()}", 500);
//            echo 'Exceção capturada: ', $e->getMessage(), "\n";
            }
        } else {
            $backtrace = debug_backtrace();
            echo_error("O Arquivo <b>$controller_path</b> n&atilde;o foi encontrado!<br> Verifique as rotas ou se o arquivo existe e suas permiss&otilde;es.<p><b>execute_action(\"{$controller}\",\"{$action}\")</b> em {$backtrace[0]['file']} na linha {$backtrace[0]['line']}</p>", 404);
        }
    }

}

if (!function_exists('pr')) {

    /**
     * Imprime uma variável da tela, seja em print_r() ou var_dump().
     *
     * @param (string/array/object) $var
     * @param (boolean) $var_dump
     */
    function pr($var, $var_dump = false) {
        echo "<pre>";
        if ($var_dump) {
            var_dump($var);
        } else {
            print_r($var);
        }
        echo "</pre>";
    }

}

if (!function_exists('hash_id')) {

    function hash_id($id) {
        return Seed::gerar($id);
    }

}

if (!function_exists('unhash_id')) {

    function unhash_id($hash) {
        return Seed::decodificar($hash);
    }

}

if (!function_exists('echo_error')) {

    function echo_error($error_message, $num_error = null, $die_after = true) {
        global $openMVCRunFromConsole;
        if (!class_exists('Controller')) {
            require_once 'loader.php';
            require_once 'controllers.php';
        }
        $c = new Controller;
        if (isset($openMVCRunFromConsole) && $openMVCRunFromConsole) {
            console_output(file_get_contents(__DIR__ . "/templates/console_error.txt") . get_status_header_desc($num_error) . " ({$num_error}) - ", "light_red", true);
            if (OPENMVC_DEBUG) {
                console_output(html_entity_decode(strip_tags($error_message)) . "\n\n", "yellow", true);
            } else {
                echo "\n";
            }
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . " {$num_error} " . get_status_header_desc($num_error), true, $num_error);
            echo parse_view_console($c->view("../app/core/templates/error", ["error_title" => get_status_header_desc($num_error), "error_message" => $error_message, "num_error" => $num_error], true));
        }
        if ($die_after) {
            die();
        }
    }

}
if (!function_exists('slugify')) {

    function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

}
if (!function_exists('generate_file_upload_name')) {

    function generate_file_upload_name($name) {
        if (file_exists($name) === TRUE) {
            $tmp_exploded = explode("/", $name);
            end($tmp_exploded);
            $last_key = key($tmp_exploded);
            $index_number = strstr($tmp_exploded[$last_key], "-", TRUE);
            if ($index_number !== FALSE &&
                    is_numeric($index_number)) {
                $next = (int) $index_number + 1;
                $tmp_exploded[$last_key] = str_replace($index_number . "-", $next . "-", $tmp_exploded[$last_key]);
            } else {
                $next = 1;
                $tmp_exploded[$last_key] = $next . "-" . $tmp_exploded[$last_key];
            }
            $name = implode("/", $tmp_exploded);
            return generate_file_upload_name($name);
        } else {
            $tmp1_exploded = explode("/", $name);
            end($tmp1_exploded);
            $last1_key = key($tmp1_exploded);
            $retorno = array("file_path" => $name, "file_name" => $tmp1_exploded[$last1_key]);
            return (object) $retorno;
        }
    }

}

