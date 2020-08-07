
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

class Multimysql {

    /**
     * Whether to show SQL/DB errors
     *
     * @since 0.71
     * @access private
     * @var bool
     */
    var $show_errors = false;

    /**
     * Whether to suppress errors during the DB bootstrapping.
     *
     * @access private
     * @since {@internal Version Unknown}}
     * @var bool
     */
    var $suppress_errors = false;

    /**
     * The last error during query.
     *
     * @since {@internal Version Unknown}}
     * @var string
     */
    var $last_error = '';

    /**
     * Amount of queries made
     *
     * @since 1.2.0
     * @access private
     * @var int
     */
    var $num_queries = 0;

    /**
     * Saved result of the last query made
     *
     * @since 1.2.0
     * @access private
     * @var array
     */
    var $last_query;

    /**
     * Saved info on the table column
     *
     * @since 1.2.0
     * @access private
     * @var array
     */
    var $col_info;

    /**
     * Saved queries that were executed
     *
     * @since 1.5.0
     * @access private
     * @var array
     */
    var $queries;

    /**
     * WordPress table prefix
     *
     * You can set this to have multiple WordPress installations
     * in a single database. The second reason is for possible
     * security precautions.
     *
     * @since 0.71
     * @access private
     * @var string
     */
    var $prefix = '';

    /**
     * Whether the database queries are ready to start executing.
     *
     * @since 2.5.0
     * @access private
     * @var bool
     */
    var $ready = false;

    /**
     * WordPress Posts table
     *
     * @since 1.5.0
     * @access public
     * @var string
     */
    var $posts;

    /**
     * WordPress Users table
     *
     * @since 1.5.0
     * @access public
     * @var string
     */
    var $users;

    /**
     * WordPress Categories table
     *
     * @since 1.5.0
     * @access public
     * @var string
     */
    var $categories;

    /**
     * WordPress Post to Category table
     *
     * @since 1.5.0
     * @access public
     * @var string
     */
    var $post2cat;

    /**
     * WordPress Comments table
     *
     * @since 1.5.0
     * @access public
     * @var string
     */
    var $comments;

    /**
     * WordPress Links table
     *
     * @since 1.5.0
     * @access public
     * @var string
     */
    var $links;

    /**
     * WordPress Options table
     *
     * @since 1.5.0
     * @access public
     * @var string
     */
    var $options;

    /**
     * WordPress Post Metadata table
     *
     * @since {@internal Version Unknown}}
     * @access public
     * @var string
     */
    var $postmeta;

    /**
     * WordPress User Metadata table
     *
     * @since 2.3.0
     * @access public
     * @var string
     */
    var $usermeta;

    /**
     * WordPress Terms table
     *
     * @since 2.3.0
     * @access public
     * @var string
     */
    var $terms;

    /**
     * WordPress Term Taxonomy table
     *
     * @since 2.3.0
     * @access public
     * @var string
     */
    var $term_taxonomy;

    /**
     * WordPress Term Relationships table
     *
     * @since 2.3.0
     * @access public
     * @var string
     */
    var $term_relationships;

    /**
     * List of WordPress tables
     *
     * @since {@internal Version Unknown}}
     * @access private
     * @var array
     */
    var $tables = array();

    /**
     * Format specifiers for DB columns. Columns not listed here default to %s.  Initialized in wp-settings.php.
     *
     * Keys are colmn names, values are format types: 'ID' => '%d'
     *
     * @since 2.8.0
     * @see wpdb:prepare()
     * @see wpdb:insert()
     * @see wpdb:update()
     * @access public
     * @war array
     */
    var $field_types = array();

    /**
     * Database table columns charset
     *
     * @since 2.2.0
     * @access public
     * @var string
     */
    var $charset;

    /**
     * Database table columns collate
     *
     * @since 2.2.0
     * @access public
     * @var string
     */
    var $collate;

    /**
     * Whether to use mysql_real_escape_string
     *
     * @since 2.8.0
     * @access public
     * @var bool
     */
    var $real_escape = false;

    /**
     * Connects to the database server and selects a database
     *
     * PHP5 style constructor for compatibility with PHP5. Does
     * the actual setting up of the class properties and connection
     * to the database.
     *
     * @since 2.0.8
     *
     * @param string $thisuser MySQL database user
     * @param string $connection_data [
     *      user     - MySQL database user
     *      password - MySQL database password
     *      name     - MySQL database name
     *      host     - MySQL database host
     * ] 
     */
    function __construct($connection_settings = null) {
        $error = null;
        if (empty($connection_settings)) {
            $error = "Connection settings is empty on contructor!<br>\n";
        } else {
            foreach (["user", "password", "host", "name"] as $value) {
                if (!array_key_exists($value, $connection_settings)) {
                    $error = "Variable `{$value}` is required on Multimysql!<br>\n";
                }
            }
        }
        if ($error) {
            echo_error($error . "Use: \$this->load('components', 'Multimysql', [\n\t'user'=>'xxx',<br>\n\t'password'=>'xxx',<br>\n\t'host'=>'xxx',<br>\n\t'name'=>'xxx'\n     ]);", 500);
        }

        register_shutdown_function(array(&$this, "__destruct"));
        if (defined('WP_DEBUG') and WP_DEBUG == true)
            $this->show_errors();

        if (defined('DB_CHARSET'))
            $this->charset = DB_CHARSET;

        if (defined('DB_COLLATE'))
            $this->collate = DB_COLLATE;

        $this->dbuser = $connection_settings['user'];
        $this->dbpassword = $connection_settings['password'];
        $this->dbhost = $connection_settings['host'];
        $this->dbname = $connection_settings['name'];

        $this->connecttodb();
        return $this;
    }

    public function change_connection($thisuser = '', $thispassword = '', $thisname = '', $thishost = '') {
        if (!empty($thisuser)) {
            $this->dbuser = $thisuser;
        }
        if (!empty($thispassword)) {
            $this->dbpassword = $thispassword;
        }
        if (!empty($thishost)) {
            $this->dbhost = $thishost;
        }
        if (!empty($thisname)) {
            $this->dbname = $thisname;
        }
        $this->connecttodb();
        return $this;
    }

    public function connecttodb() {
        $this->dbh = @mysqli_connect($this->dbhost, $this->dbuser, $this->dbpassword);
        if (!$this->dbh) {
            $this->bail(sprintf(/* WP_I18N_DB_CONN_ERROR */"
<h1>Error establishing a database connection</h1>
<p>This either means that the username and password information is incorrect or we can't contact the database server at <code>%s</code>. This could mean your host's database server is down.</p>
<ul>
	<li>Are you sure you have the correct username and password?</li>
	<li>Are you sure that you have typed the correct hostname?</li>
	<li>Are you sure that the database server is running?</li>
</ul>
<p>If you're unsure what these terms mean you should probably contact your host. If you still need help you can always visit the <a href='http://wordpress.org/support/'>WordPress Support Forums</a>.</p>
"/* /WP_I18N_DB_CONN_ERROR */, $thishost));
            return;
        }

        $this->ready = true;

        $this->initialquery = 1;
        // $this->query("set session wait_timeout=600");

        if ($this->has_cap('collation')) {
            if (!empty($this->charset)) {
                if (function_exists('mysqli_set_charset')) {
                    mysqli_set_charset($this->dbh, $this->charset);
                    $this->real_escape = true;
                } else {
                    $collation_query = "SET NAMES '{$this->charset}'";
                    if (!empty($this->collate))
                        $collation_query .= " COLLATE '{$this->collate}'";
                    $this->query($collation_query);
                }
            }
        }

        $this->initialquery = 0;

        $this->select($this->dbname);
    }

    public function queryWithReconnect($query) {
        $maxcount = 5;
        $cnt = 1;

        while ($cnt < $maxcount) {
            @mysqli_close($this->dbh);
            $this->connecttodb();
            $this->result = @mysqli_query($this->dbh, $query);
            if (mysqli_error($this->dbh)) {
                
            } else {
                return 0;  // 0 means it passed.
            }
            $cnt += 1;
        }

        return 1;  // Nonzero means fail.
    }

    /**
     * PHP5 style destructor and will run when database object is destroyed.
     *
     * @since 2.0.8
     *
     * @return bool Always true
     */
    public function __destruct() {
        @mysqli_close($this->dbh);
        return true;
    }

    /**
     * Sets the table prefix for the WordPress tables.
     *
     * Also allows for the CUSTOM_USER_TABLE and CUSTOM_USER_META_TABLE to
     * override the WordPress users and usersmeta tables that would otherwise be determined by the $prefix.
     *
     * @since 2.5.0
     *
     * @param string $prefix Alphanumeric name for the new prefix.
     * @return string|WP_Error Old prefix or WP_Error on error
     */
    public function set_prefix($prefix) {

        if (preg_match('|[^a-z0-9_]|i', $prefix))
            return new WP_Error('invalid_db_prefix', /* WP_I18N_DB_BAD_PREFIX */ 'Invalid database prefix'/* /WP_I18N_DB_BAD_PREFIX */);

        $old_prefix = $this->prefix;
        $this->prefix = $prefix;
        $this->base_prefix = $prefix;
        foreach ((array) $this->tables as $table)
            $this->$table = $this->prefix . $table;

        if (defined('CUSTOM_USER_TABLE'))
            $this->users = CUSTOM_USER_TABLE;

        if (defined('CUSTOM_USER_META_TABLE'))
            $this->usermeta = CUSTOM_USER_META_TABLE;

        return $old_prefix;
    }

    /**
     * Sets the table prefix for the WordPress tables.
     *
     * @since 2.5.0
     *
     * @param string $prefix Alphanumeric name for the new prefix.
     * @return string|WP_Error Old prefix or WP_Error on error
     */
//   public  function set_prefix($prefix, $set_table_names = true) {
//
//        if (preg_match('|[^a-z0-9_]|i', $prefix))
//            return new WP_Error('invalid_db_prefix', /* WP_I18N_DB_BAD_PREFIX */'Prefixo de banco de dados inválido'/* /WP_I18N_DB_BAD_PREFIX */);
//
//        $old_prefix = is_multisite() ? '' : $prefix;
//
//        if (isset($this->base_prefix))
//            $old_prefix = $this->base_prefix;
//
//        $this->base_prefix = $prefix;
//
//        if ($set_table_names) {
//            foreach ($this->tables('global') as $table => $prefixed_table)
//                $this->$table = $prefixed_table;
//
//            if (is_multisite() && empty($this->blogid))
//                return $old_prefix;
//
//            $this->prefix = $this->get_blog_prefix();
//
//            foreach ($this->tables('blog') as $table => $prefixed_table)
//                $this->$table = $prefixed_table;
//
//            foreach ($this->tables('old') as $table => $prefixed_table)
//                $this->$table = $prefixed_table;
//        }
//        return $old_prefix;
//    }

    /**
     * Selects a database using the current database connection.
     *
     * The database name will be changed based on the current database
     * connection. On failure, the execution will bail and display an DB error.
     *
     * @since 0.71
     *
     * @param string $this MySQL database name
     * @return null Always null.
     */
    public function select($db) {
        if (!@mysqli_select_db($this->dbh, $db)) {
            $this->ready = false;
            $this->bail(sprintf(/* WP_I18N_DB_SELECT_DB */'
<h1>Can&#8217;t select database</h1>
<p>We were able to connect to the database server (which means your username and password is okay) but not able to select the <code>%1$s</code> database.</p>
<ul>
<li>Are you sure it exists?</li>
<li>Does the user <code>%2$s</code> have permission to use the <code>%1$s</code> database?</li>
<li>On some systems the name of your database is prefixed with your username, so it would be like <code>username_%1$s</code>. Could that be the problem?</li>
</ul>
<p>If you don\'t know how to setup a database you should <strong>contact your host</strong>. If all else fails you may find help at the <a href="http://wordpress.org/support/">WordPress Support Forums</a>.</p>'/* /WP_I18N_DB_SELECT_DB */, $this, DB_USER));
            return;
        }
    }

    public function _weak_escape($string) {
        return addslashes($string);
    }

    public function _real_escape($string) {
        if ($this->dbh && $this->real_escape)
            return mysqli_real_escape_string($this->dbh, $string);
        else
            return addslashes($string);
    }

    public function _escape($data) {
        if (is_array($data)) {
            foreach ((array) $data as $k => $v) {
                if (is_array($v))
                    $data[$k] = $this->_escape($v);
                else
                    $data[$k] = $this->_real_escape($v);
            }
        } else {
            $data = $this->_real_escape($data);
        }

        return $data;
    }

    /**
     * Escapes content for insertion into the database using addslashes(), for security
     *
     * @since 0.71
     *
     * @param string|array $data
     * @return string query safe string
     */
    public function escape($data) {
        if (is_array($data)) {
            foreach ((array) $data as $k => $v) {
                if (is_array($v))
                    $data[$k] = $this->escape($v);
                else
                    $data[$k] = $this->_weak_escape($v);
            }
        } else {
            $data = $this->_weak_escape($data);
        }

        return $data;
    }

    /**
     * Escapes content by reference for insertion into the database, for security
     *
     * @since 2.3.0
     *
     * @param string $s
     */
    public function escape_by_ref(&$string) {
        $string = $this->_real_escape($string);
    }

    /**
     * Prepares a SQL query for safe execution.  Uses sprintf()-like syntax.
     *
     * Thispublic  function only supports a small subset of the sprintf syntax; it only supports %d (decimal number), %s (string).
     * Does not support sign, padding, alignment, width or precision specifiers.
     * Does not support argument numbering/swapping.
     *
     * May be called like {@link http://php.net/sprintf sprintf()} or like {@link http://php.net/vsprintf vsprintf()}.
     *
     * Both %d and %s should be left unquoted in the query string.
     *
     * <code>
     * wpdb::prepare( "SELECT * FROM `table` WHERE `column` = %s AND `field` = %d", "foo", 1337 )
     * </code>
     *
     * @link http://php.net/sprintf Description of syntax.
     * @since 2.3.0
     *
     * @param string $query Query statement with sprintf()-like placeholders
     * @param array|mixed $args The array of variables to substitute into the query's placeholders if being called like {@link http://php.net/vsprintf vsprintf()}, or the first variable to substitute into the query's placeholders if being called like {@link http://php.net/sprintf sprintf()}.
     * @param mixed $args,... further variables to substitute into the query's placeholders if being called like {@link http://php.net/sprintf sprintf()}.
     * @return null|string Sanitized query string
     */
    public function prepare($query = null) { // ( $query, *$args )
        if (is_null($query))
            return;
        $args = func_get_args();
        array_shift($args);
        // If args were passed as an array (as in vsprintf), move them up
        if (isset($args[0]) && is_array($args[0]))
            $args = $args[0];
        $query = str_replace("'%s'", '%s', $query); // in case someone mistakenly already singlequoted it
        $query = str_replace('"%s"', '%s', $query); // doublequote unquoting
        $query = str_replace('%s', "'%s'", $query); // quote the strings
        array_walk($args, array(&$this, 'escape_by_ref'));
        return @vsprintf($query, $args);
    }

    /**
     * Print SQL/DB error.
     *
     * @since 0.71
     * @global array $EZSQL_ERROR Stores error information of query and error string
     *
     * @param string $str The error to display
     * @return bool False if the showing of errors is disabled.
     */
    public function print_error($str = '') {
        global $EZSQL_ERROR;

        if (!$str)
            $str = mysqli_error($this->dbh);
        $EZSQL_ERROR[] = array('query' => $this->last_query, 'error_str' => $str);

        if ($this->suppress_errors)
            return false;
        if (!empty($openMVCRunFromConsole))
            $error_str = console_output("OpenMVC database error::\n", "red", true);
        if ($caller = $this->get_caller())
            $error_str .= sprintf(/* WP_I18N_DB_QUERY_ERROR_FULL */ '%1$s for query %2$s made by %3$s'/* /WP_I18N_DB_QUERY_ERROR_FULL */, $str, $this->last_query, $caller);
        else
            $error_str .= sprintf(/* WP_I18N_DB_QUERY_ERROR */ '%1$s for query %2$s'/* /WP_I18N_DB_QUERY_ERROR */, $str, $this->last_query);

        $log_error = true;
        if (!function_exists('error_log'))
            $log_error = false;

        $log_file = @ini_get('error_log');
        if (!empty($log_file) && ('syslog' != $log_file) && !@is_writable($log_file))
            $log_error = false;

        if ($log_error)
            @parse_view_console(error_log($error_str, 0));

        // Is error output turned on or not..
        if (!$this->show_errors)
            return false;

        $str = htmlspecialchars($str, ENT_QUOTES);
        $query = htmlspecialchars($this->last_query, ENT_QUOTES);

        // If there is an error then take note of it
        $print = "<div id='error'>
		<p class='wpdberror'><strong>OpenMVC database error:</strong> [$str]<br />
		<code>$query</code></p>
		</div>";
        echo_error($print);
    }

    /**
     * Enables showing of database errors.
     *
     * Thispublic  function should be used only to enable showing of errors.
     * wpdb::hide_errors() should be used instead for hiding of errors. However,
     * thispublic  function can be used to enable and disable showing of database
     * errors.
     *
     * @since 0.71
     *
     * @param bool $show Whether to show or hide errors
     * @return bool Old value for showing errors.
     */
    public function show_errors($show = true) {
        $errors = $this->show_errors;
        $this->show_errors = $show;
        return $errors;
    }

    /**
     * Disables showing of database errors.
     *
     * @since 0.71
     *
     * @return bool Whether showing of errors was active or not
     */
    public function hide_errors() {
        $show = $this->show_errors;
        $this->show_errors = false;
        return $show;
    }

    /**
     * Whether to suppress database errors.
     *
     * @param unknown_type $suppress
     * @return unknown
     */
    public function suppress_errors($suppress = true) {
        $errors = $this->suppress_errors;
        $this->suppress_errors = $suppress;
        return $errors;
    }

    /**
     * Kill cached query results.
     *
     * @since 0.71
     */
    public function flush() {
        $this->last_result = array();
        $this->col_info = null;
        $this->last_query = null;
    }

    /**
     * Perform a MySQL database query, using current database connection.
     *
     * More information can be found on the codex page.
     *
     * @since 0.71
     *
     * @param string $query
     * @return int|false Number of rows affected/selected or false on error
     */
    public function query($query) {
        if (defined("DEBUG_QUERY") && DEBUG_QUERY)
            echo $query;
        if (defined("AGENCE_LOG_QUERY") && AGENCE_LOG_QUERY)
            new gerar_log(AGENCE_LOG_QUERY, $query);
        if (!$this->ready)
            return false;

        // filter the query, if filters are available
        // NOTE: some queries are made before the plugins have been loaded, and thus cannot be filtered with this method
        if (function_exists('apply_filters'))
            $query = apply_filters('query', $query);

        // initialise return
        $return_val = 0;
        $this->flush();

        // Log how thepublic  function was called
        $this->func_call = "\$this->query(\"$query\")";

        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Perform the query via std mysql_query function..
        if (defined('SAVEQUERIES') && SAVEQUERIES)
            $this->timer_start();

        $this->result = @mysqli_query($this->dbh, $query);
        ++$this->num_queries;

        if (defined('SAVEQUERIES') && SAVEQUERIES)
            $this->queries[] = array($query, $this->timer_stop(), $this->get_caller());

        // If there is an error then take note of it..
        if ($this->last_error = mysqli_error($this->dbh)) {
            if (($this->initialquery) || ($this->queryWithReconnect($query) != 0)) {
                $this->print_error();
                return false;
            }
        }

        if (preg_match("/^\\s*(insert|delete|update|replace|alter) /i", $query)) {
            $this->rows_affected = mysqli_affected_rows($this->dbh);
            // Take note of the insert_id
            if (preg_match("/^\\s*(insert|replace) /i", $query)) {
                $this->insert_id = mysqli_insert_id($this->dbh);
            }
            // Return number of rows affected
            $return_val = $this->rows_affected;
        } else {
            $i = 0;
            while ($i < @mysqli_num_fields($this->result)) {
                $this->col_info[$i] = @mysqli_fetch_field($this->result);
                $i++;
            }
            $num_rows = 0;
            while ($row = @mysqli_fetch_object($this->result)) {
                $this->last_result[$num_rows] = $row;
                $num_rows++;
            }

            @mysqli_free_result($this->result);
//            $return_val = $this->last_result;
            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;
        }
//        print_r($query);
//        echo"<br/>";
//        print_r($this->last_result);
//        echo"<br/>";

        return $return_val;
    }

    /**
     * Insert a row into a table.
     *
     * <code>
     * wpdb::insert( 'table', array( 'column' => 'foo', 'field' => 1337 ), array( '%s', '%d' ) )
     * </code>
     *
     * @since 2.5.0
     * @see wpdb::prepare()
     *
     * @param string $table table name
     * @param array $data Data to insert (in column => value pairs).  Both $data columns and $data values should be "raw" (neither should be SQL escaped).
     * @param array|string $format (optional) An array of formats to be mapped to each of the value in $data.  If string, that format will be used for all of the values in $data.  A format is one of '%d', '%s' (decimal number, string).  If omitted, all values in $data will be treated as strings.
     * @return int|false The number of rows inserted, or false on error.
     */
    public function insert($table, $data, $format = null) {
        $formats = $format = (array) $format;
        $fields = array_keys($data);
        $formatted_fields = array();
        foreach ($fields as $field) {
            if (!empty($format))
                $form = ( $form = array_shift($formats) ) ? $form : $format[0];
            elseif (isset($this->field_types[$field]))
                $form = $this->field_types[$field];
            else
                $form = '%s';
            $formatted_fields[] = $form;
        }
        $sql = "INSERT INTO `$table` (`" . implode('`,`', $fields) . "`) VALUES ('" . implode("','", $formatted_fields) . "')";
        $this->setNulls($table, $data);
        return $this->query(str_replace("'NULL'", 'NULL', $this->prepare($sql, array_merge(array_values($data)))));
    }

    private function setNulls($table, &$data) {
        if (
                is_array($data) &&
                !empty($data) &&
                !empty($table)
        ) {
            $describe = $this->get_results("DESCRIBE " . $table);
            foreach ($data as $field => &$value) {
                foreach ($describe as $desc) {
                    if (
                            $value == '' &&
                            $desc->Field == $field &&
                            $desc->Null == 'YES'
                    ) {
                        $value = "NULL";
                    }
                    unset($desc);
                }
                unset($describe);
                unset($field);
                unset($value);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Update a row in the table
     *
     * <code>
     * wpdb::update( 'table', array( 'column' => 'foo', 'field' => 1337 ), array( 'ID' => 1 ), array( '%s', '%d' ), array( '%d' ) )
     * </code>
     *
     * @since 2.5.0
     * @see wpdb::prepare()
     *
     * @param string $table table name
     * @param array $data Data to update (in column => value pairs).  Both $data columns and $data values should be "raw" (neither should be SQL escaped).
     * @param array $where A named array of WHERE clauses (in column => value pairs).  Multiple clauses will be joined with ANDs.  Both $where columns and $where values should be "raw".
     * @param array|string $format (optional) An array of formats to be mapped to each of the values in $data.  If string, that format will be used for all of the values in $data.  A format is one of '%d', '%s' (decimal number, string).  If omitted, all values in $data will be treated as strings.
     * @param array|string $format_where (optional) An array of formats to be mapped to each of the values in $where.  If string, that format will be used for all of  the items in $where.  A format is one of '%d', '%s' (decimal number, string).  If omitted, all values in $where will be treated as strings.
     * @return int|false The number of rows updated, or false on error.
     */
    public function update($table, $data, $where, $format = null, $where_format = null) {
        if (!is_array($where))
            return false;

        $formats = $format = (array) $format;
        $bits = $wheres = array();
        foreach ((array) array_keys($data) as $field) {
            if (!empty($format))
                $form = ( $form = array_shift($formats) ) ? $form : $format[0];
            elseif (isset($this->field_types[$field]))
                $form = $this->field_types[$field];
            else
                $form = '%s';

            $bits[] = "`$field` = {$form}";
        }

//        foreach ($bits as $key => $value) {
//            if ($key == "id" || $key == "ID")
//                continue;
//
//            if ($value === NULL || strtoupper($value) === 'NULL') {
//                $fields[] = "{$key} = NULL";
//            } else {
//                $fields[] = "{$key} = " . ((is_numeric($value)) ? "%d" : "%s");
//                $values[] = $value;
//            }
//        }


        $where_formats = $where_format = (array) $where_format;
        foreach ((array) array_keys($where) as $field) {
            if (!empty($where_format))
                $form = ( $form = array_shift($where_formats) ) ? $form : $where_format[0];
            elseif (isset($this->field_types[$field]))
                $form = $this->field_types[$field];
            else
                $form = '%s';
            $wheres[] = "`$field` = {$form}";
        }

        $sql = "UPDATE `$table` SET " . implode(', ', $bits) . ' WHERE ' . implode(' AND ', $wheres);
        $this->setNulls($table, $data);
        return $this->query(str_replace("'NULL'", 'NULL', $this->prepare($sql, array_merge(array_values($data), array_values($where)))));
    }

    /**
     * Retrieve one variable from the database.
     *
     * Executes a SQL query and returns the value from the SQL result.
     * If the SQL result contains more than one column and/or more than one row, thispublic  function returns the value in the column and row specified.
     * If $query is null, thispublic  function returns the value in the specified column and row from the previous SQL result.
     *
     * @since 0.71
     *
     * @param string|null $query SQL query.  If null, use the result from the previous query.
     * @param int $x (optional) Column of value to return.  Indexed from 0.
     * @param int $y (optional) Row of value to return.  Indexed from 0.
     * @return string Database query result
     */
    public function get_var($query = null, $x = 0, $y = 0) {
        $this->func_call = "\$this->get_var(\"$query\",$x,$y)";
        if ($query)
            $this->query($query);

        // Extract var out of cached results based x,y vals
        if (!empty($this->last_result[$y])) {
            $values = array_values(get_object_vars($this->last_result[$y]));
        }

        // If there is a value return it else return null
        return (isset($values[$x]) && $values[$x] !== '') ? $values[$x] : null;
    }

    /**
     * Retrieve one row from the database.
     *
     * Executes a SQL query and returns the row from the SQL result.
     *
     * @since 0.71
     *
     * @param string|null $query SQL query.
     * @param string $output (optional) one of ARRAY_A | ARRAY_N | OBJECT constants.  Return an associative array (column => value, ...), a numerically indexed array (0 => value, ...) or an object ( ->column = value ), respectively.
     * @param int $y (optional) Row to return.  Indexed from 0.
     * @return mixed Database query result in format specifed by $output
     */
    public function get_row($query = null, $output = OBJECT, $y = 0) {
        $this->func_call = "\$this->get_row(\"$query\",$output,$y)";
        if ($query)
            $this->query($query);
        else
            return null;

        if (!isset($this->last_result[$y]))
            return null;

        if ($output == OBJECT) {
            return $this->last_result[$y] ? $this->last_result[$y] : null;
        } elseif ($output == ARRAY_A) {
            return $this->last_result[$y] ? get_object_vars($this->last_result[$y]) : null;
        } elseif ($output == ARRAY_N) {
            return $this->last_result[$y] ? array_values(get_object_vars($this->last_result[$y])) : null;
        } else {
            $this->print_error(/* WP_I18N_DB_GETROW_ERROR */" \$this->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N"/* /WP_I18N_DB_GETROW_ERROR */);
        }
    }

    /**
     * Retrieve one column from the database.
     *
     * Executes a SQL query and returns the column from the SQL result.
     * If the SQL result contains more than one column, thispublic  function returns the column specified.
     * If $query is null, thispublic  function returns the specified column from the previous SQL result.
     *
     * @since 0.71
     *
     * @param string|null $query SQL query.  If null, use the result from the previous query.
     * @param int $x Column to return.  Indexed from 0.
     * @return array Database query result.  Array indexed from 0 by SQL result row number.
     */
    public function get_col($query = null, $x = 0) {
        if ($query)
            $this->query($query);

        $new_array = array();
        // Extract the column values
        for ($i = 0; $i < count($this->last_result); $i++) {
            $new_array[$i] = $this->get_var(null, $x, $i);
        }
        return $new_array;
    }

    /**
     * Retrieve an entire SQL result set from the database (i.e., many rows)
     *
     * Executes a SQL query and returns the entire SQL result.
     *
     * @since 0.71
     *
     * @param string $query SQL query.
     * @param string $output (optional) ane of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.  With one of the first three, return an array of rows indexed from 0 by SQL result row number.  Each row is an associative array (column => value, ...), a numerically indexed array (0 => value, ...), or an object. ( ->column = value ), respectively.  With OBJECT_K, return an associative array of row objects keyed by the value of each row's first column's value.  Duplicate keys are discarded.
     * @return mixed Database query results
     */
    public function get_results($query = null, $output = OBJECT) {
        $this->func_call = "\$this->get_results(\"$query\", $output)";

        if ($query)
            $this->query($query);
        else
            return null;

        if ($output == OBJECT) {
            // Return an integer-keyed array of row objects
            return $this->last_result;
        } elseif ($output == OBJECT_K) {
            // Return an array of row objects with keys from column 1
            // (Duplicates are discarded)
            foreach ($this->last_result as $row) {
                $key = array_shift(get_object_vars($row));
                if (!isset($new_array[$key]))
                    $new_array[$key] = $row;
            }
            return $new_array;
        } elseif ($output == ARRAY_A || $output == ARRAY_N) {
            // Return an integer-keyed array of...
            if ($this->last_result) {
                $i = 0;
                foreach ((array) $this->last_result as $row) {
                    if ($output == ARRAY_N) {
                        // ...integer-keyed row arrays
                        $new_array[$i] = array_values(get_object_vars($row));
                    } else {
                        // ...column name-keyed row arrays
                        $new_array[$i] = get_object_vars($row);
                    }
                    ++$i;
                }
                return $new_array;
            }
        }
    }

    /**
     * Retrieve column metadata from the last query.
     *
     * @since 0.71
     *
     * @param string $info_type one of name, table, def, max_length, not_null, primary_key, multiple_key, unique_key, numeric, blob, type, unsigned, zerofill
     * @param int $col_offset 0: col name. 1: which table the col's in. 2: col's max length. 3: if the col is numeric. 4: col's type
     * @return mixed Column Results
     */
    public function get_col_info($info_type = 'name', $col_offset = -1) {
        if ($this->col_info) {
            if ($col_offset == -1) {
                $i = 0;
                foreach ((array) $this->col_info as $col) {
                    $new_array[$i] = $col->{$info_type};
                    $i++;
                }
                return $new_array;
            } else {
                return $this->col_info[$col_offset]->{$info_type};
            }
        }
    }

    /**
     * Starts the timer, for debugging purposes.
     *
     * @since 1.5.0
     *
     * @return true
     */
    public function timer_start() {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $this->time_start = $mtime[1] + $mtime[0];
        return true;
    }

    /**
     * Stops the debugging timer.
     *
     * @since 1.5.0
     *
     * @return int Total time spent on the query, in milliseconds
     */
    public function timer_stop() {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $time_end = $mtime[1] + $mtime[0];
        $time_total = $time_end - $this->time_start;
        return $time_total;
    }

    /**
     * Wraps errors in a nice header and footer and dies.
     *
     * Will not die if wpdb::$show_errors is true
     *
     * @since 1.5.0
     *
     * @param string $message
     * @return false|void
     */
    public function bail($message) {
        if (!$this->show_errors) {
            if (class_exists('WP_Error'))
                $this->error = new WP_Error('500', $message);
            else
                $this->error = $message;
            return false;
        }
        wp_die($message);
    }

    /**
     * Whether or not MySQL database is at least the required minimum version.
     *
     * @since 2.5.0
     * @uses $wp_version
     *
     * @return WP_Error
     */
    public function check_database_version() {
        global $wp_version;
        // Make sure the server has MySQL 4.0
        if (version_compare($this->db_version(), '4.0.0', '<'))
            return new WP_Error('database_version', sprintf(__('<strong>ERROR</strong>: WordPress %s requires MySQL 4.0.0 or higher'), $wp_version));
    }

    /**
     * Whether of not the database supports collation.
     *
     * Called when WordPress is generating the table scheme.
     *
     * @since 2.5.0
     *
     * @return bool True if collation is supported, false if version does not
     */
    public function supports_collation() {
        return $this->has_cap('collation');
    }

    /**
     * Genericpublic  function to determine if a database supports a particular feature
     * @param string $this_cap the feature
     * @param false|string|resource $thish_or_table (not implemented) Which database to test.  False = the currently selected database, string = the database containing the specified table, resource = the database corresponding to the specified mysql resource.
     * @return bool
     */
    public function has_cap($this_cap) {
        $version = $this->db_version();

        switch (strtolower($this_cap)) :
            case 'collation' :    // @since 2.5.0
            case 'group_concat' : // @since 2.7
            case 'subqueries' :   // @since 2.7
                return version_compare($version, '4.1', '>=');
                break;
        endswitch;

        return false;
    }

    /**
     * Retrieve the name of thepublic  function that called wpdb.
     *
     * Requires PHP 4.3 and searches up the list of functions until it reaches
     * the one that would most logically had called this method.
     *
     * @since 2.5.0
     *
     * @return string The name of the calling function
     */
    public function get_caller() {
        // requires PHP 4.3+
        if (!is_callable('debug_backtrace'))
            return '';

        $bt = debug_backtrace();
        $caller = array();

        $bt = array_reverse($bt);
        foreach ((array) $bt as $call) {
            if (@$call['class'] == __CLASS__)
                continue;
            $function = $call['function'];
            if (isset($call['class']))
                $function = $call['class'] . "->$function";
            $caller[] = $function;
        }
        $caller = join(', ', $caller);

        return $caller;
    }

    /**
     * The database version number
     * @param false|string|resource $thish_or_table (not implemented) Which database to test.  False = the currently selected database, string = the database containing the specified table, resource = the database corresponding to the specified mysql resource.
     * @return false|string false on failure, version number on success
     */
    public function db_version() {
        return preg_replace('/[^0-9.].*/', '', mysqli_get_server_info($this->dbh));
    }

    /**
     * Gets blog prefix.
     *
     * @uses is_multisite()
     * @since 3.0.0
     * @param int $blog_id Optional.
     * @return string Blog prefix.
     */
    public function get_blog_prefix($blog_id = null) {
        if (is_multisite()) {
            if (null === $blog_id)
                $blog_id = $this->blogid;
            if (defined('MULTISITE') && ( 0 == $blog_id || 1 == $blog_id ))
                return $this->base_prefix;
            else
                return $this->base_prefix . $blog_id . '_';
        } else {
            return $this->base_prefix;
        }
    }

    /**
     * Returns an array of WordPress tables.
     *
     * Also allows for the CUSTOM_USER_TABLE and CUSTOM_USER_META_TABLE to
     * override the WordPress users and usersmeta tables that would otherwise
     * be determined by the prefix.
     *
     * The scope argument can take one of the following:
     *
     * 'all' - returns 'all' and 'global' tables. No old tables are returned.
     * 'blog' - returns the blog-level tables for the queried blog.
     * 'global' - returns the global tables for the installation, returning multisite tables only if running multisite.
     * 'ms_global' - returns the multisite global tables, regardless if current installation is multisite.
     * 'old' - returns tables which are deprecated.
     *
     * @since 3.0.0
     * @uses wpdb::$tables
     * @uses wpdb::$old_tables
     * @uses wpdb::$global_tables
     * @uses wpdb::$ms_global_tables
     * @uses is_multisite()
     *
     * @param string $scope Optional. Can be all, global, ms_global, blog, or old tables. Defaults to all.
     * @param bool $prefix Optional. Whether to include table prefixes. Default true. If blog
     * 	prefix is requested, then the custom users and usermeta tables will be mapped.
     * @param int $blog_id Optional. The blog_id to prefix. Defaults to wpdb::$blogid. Used only when prefix is requested.
     * @return array Table names. When a prefix is requested, the key is the unprefixed table name.
     */
    public function tables($scope = 'all', $prefix = true, $blog_id = 0) {
        switch ($scope) {
            case 'all' :
                $tables = array_merge($this->global_tables, $this->tables);
                if (is_multisite())
                    $tables = array_merge($tables, $this->ms_global_tables);
                break;
            case 'blog' :
                $tables = $this->tables;
                break;
            case 'global' :
                $tables = $this->global_tables;
                if (is_multisite())
                    $tables = array_merge($tables, $this->ms_global_tables);
                break;
            case 'ms_global' :
                $tables = $this->ms_global_tables;
                break;
            case 'old' :
                $tables = $this->old_tables;
                break;
            default :
                return array();
                break;
        }

        if ($prefix) {
            if (!$blog_id)
                $blog_id = $this->blogid;
            $blog_prefix = $this->get_blog_prefix($blog_id);
            $base_prefix = $this->base_prefix;
            $global_tables = array_merge($this->global_tables, $this->ms_global_tables);
            foreach ($tables as $k => $table) {
                if (in_array($table, $global_tables))
                    $tables[$table] = $base_prefix . $table;
                else
                    $tables[$table] = $blog_prefix . $table;
                unset($tables[$k]);
            }

            if (isset($tables['users']) && defined('CUSTOM_USER_TABLE'))
                $tables['users'] = CUSTOM_USER_TABLE;

            if (isset($tables['usermeta']) && defined('CUSTOM_USER_META_TABLE'))
                $tables['usermeta'] = CUSTOM_USER_META_TABLE;
        }

        return $tables;
    }

    /**
     * Deleta da tabela de acordo com o ID.
     * 
     * @param int $id
     */
    public function deletar($table, $keyValue) {
        $sql = $this->prepare('DELETE FROM ' . $table . ' where %1$s = %2$d', $keyValue);
        return $this->query($sql);
    }

    protected function generateInsert($table, $data) {
        $fieldlist = array_keys($data);
        $fields = implode(",", $fieldlist);
        $valuelist = array();
        foreach ($data as $key => $value) {
            if ($value != NULL || $value === 0 || $value === "") {
                $valuelist[] = (is_string($value)) ? "%s" : "%d";
            } else {
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

        $myWhere = $this->buildWhere($where, $join, true, $operator);

        foreach ($data as $key => $value) {
            if ($key == "id" || $key == "ID")
                continue;

            if ($value === NULL || strtoupper($value) === 'NULL') {
                $fields[] = "{$key} = NULL";
            } else {
                $fields[] = "{$key} = " . ((is_numeric($value)) ? "%d" : "%s");
                $values[] = $value;
            }
        }
        $field_and_value = implode(",", $fields);

        $sql = $this->prepare("UPDATE {$table} SET {$field_and_value} {$myWhere}", $values);
        $retrono = $this->query($sql);
        return $retrono;
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
                $fields[] = "{$key} = " . ((is_numeric($value)) ? "%d" : "%s");
                $values[] = $value;
            }
        }
        $field_and_value = implode(",", $fields);
        $values[] = $id;

        $id_field = isset($data['ID']) ? 'ID' : 'id';
        $sql = $this->prepare("UPDATE {$table} SET {$field_and_value} WHERE {$id_field} = %d", $values);
        return $this->query($sql);
    }

    public function saveWhere($table_name, $dados, $where = array()) {
        return $this->salvarOnde($table_name, $dados, $where);
    }

    public function salvarOnde($table_name, $dados, $onde = array()) {
        $id = null;
        if (is_object($dados))
            $dados = (Array) $dados;

        if (!empty($onde)) {
            $this->updateWhere($dados, $onde, "AND", "=", $table_name);
        } else {
            $this->generateInsert($table_name, $dados);
        }

        if (!empty($this->last_error)) {
            return false;
        } else {
            $id = !empty($this->insert_id) ? $this->insert_id : $id;
            return $id;
        }
    }

    public function save($table_name, $dados) {
        return $this->salvar($table_name, $dados);
    }

    public function salvar($table_name, $dados) {
        $id = null;
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
            $this->generateUpdate($table_name, $dados, $id);
        } else {
            $this->generateInsert($table_name, $dados);
        }

        if (!empty($this->last_error)) {
            return false;
        } else {
            $id = !empty($this->insert_id) ? $this->insert_id : $id;
            return $id;
        }
    }

    public function get($table_name, $id) {
        return $this->row($this->prepare("SELECT * FROM {$table_name} WHERE id = %d LIMIT 1", array($id)));
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
                        $_conditions[] = "{$key} LIKE '%{$val}%'";
                    } else if (strstr($key, " LIKE%%")) {
                        $_conditions[] = str_replace("LIKE%%", "", $key) . " LIKE '%{$val}%'";
                    } else if ($val == NULL) {
                        $_conditions[] = " $key IS NULL";
                    } else if (is_array($val) && !empty($val)) {
                        $joined_values = array();
                        $joined = false;
                        foreach ($val as $in_key => $in_val) {
                            if (is_numeric($in_val)) {
                                $joined_values[] = is_numeric($in_val) ? $in_val : "'{$in_val}'";
                                $joined = true;
                            }

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
                                $_conditions[] = "{$key} IN ({$joined_valuesSTR})";
                            }
                        }
                    } else {
//                        $_conditions[] = "(" . $this->buildWhere($params, "OR", false, $operator) . ")";
                        $_conditions[$key] = "{$key} " . (strstr($key, " ") ? "" : $operator) . (is_string($val) ? ($val == "NULL" ? $val : "'" . str_replace('"', "'", $val) . "'" ) : $val);
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

}

?>