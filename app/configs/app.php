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

/** URL aplicação web OpenMvcPHP */
define('OPENMVC_HTTP_HOST', 'openmvc.local');

/** Ativa o Debug de aplicação OpenMvcPHP */
define('OPENMVC_DEBUG', TRUE);

/** Raiz da include_path do PHP */
define("OPENMVC_TMP_FOLDER", "/tmp");

/** Limite de memória do PHP */
ini_set('memory_limit', '-1');

/** Timezone adequado a região */
define('TIMEZONE', 'America/Sao_Paulo');

/** Configurações para AWS */
define('AWS_PUBLIC_KEY', '$YOUR-AWS-PUBLIC-KEY');
define('AWS_SECRET_KEY', '$YOUR-AWS-SECRET-KEY');


/** Configurações para OneSignal */
define('ONESIGNAL_APP_IP', '$YOUR-ONESIGNAL-APP-ID');
define('ONESIGNAL_API_KEY', '$YOUR-ONESIGNAL-APP-KEY');









/** Raiz da aplicação OpenMvcPHP */
define('OPENMVC_DOCUMENT_ROOT', str_replace('app/configs', 'public', __DIR__));

/** Raiz da include_path do PHP */
define('OPENMVC_INCLUDE_PATH', OPENMVC_DOCUMENT_ROOT);
?>
