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
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<center class="text-primary">
    <h2>Bem Vindo ao OpenMVC PHP Framework</h2>
    <br/>
    <br/>
    <h4>Lista de Tabelas Encontradas no DB</h4>
</center>
<div  class="row ">
    <div  class="container">
        <?php foreach ($tables as $key => $value): ?>
            <div class="col-md-12 panel panel-primary">
                <div class="panel-body">
                    <?php $DB_KEY = "Tables_in_" . DB_NAME; ?>
                    <b><?php echo($value->$DB_KEY); ?></b>
                    <?php
                    $controller = "{$_SERVER['DOCUMENT_ROOT']}/../controllers/{$value->$DB_KEY}.php";
                    if (file_exists($controller)) {
                        ?>
                        <br>
                        <small class="text-danger">
                            <small >
                                <small >
                                    <b>CRUD ENCONTRADO</b>
                                </small>
                            </small>
                        </small>
                        &nbsp;
                        <a role="button" class="btn btn-xs btn-primary" href="/<?php echo $value->$DB_KEY; ?>/listar">Ver CRUD</a>
                    <?php } else { ?>
                        <a role="button" class="btn btn-xs btn-info" title="Clique aqui para gerar o CRUD desta Tabela" href="/?crud=<?php echo($value->$DB_KEY); ?>">Gerar Crud</a>
                        <a role="button" class="btn btn-xs btn-success" title="Clique aqui para gerar o CRUD desta Tabela" href="/?crud=<?php echo($value->$DB_KEY); ?>&bootstrap=true">Gerar Crud Bootstrap</a>
                    <?php } ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>