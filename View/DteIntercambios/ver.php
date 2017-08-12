<ul class="nav nav-pills pull-right">
<?php if (!$Emisor->config_ambiente_en_certificacion) : ?>
    <li>
        <a href="https://www4.sii.cl/registrorechazodteInternet" title="Ir al registro de aceptación o reclamos de un DTE en el SII" target="_blank">
            Aceptar/rechazar en SII
        </a>
    </li>
<?php endif; ?>
<?php if ($DteIntercambio->codigo!=1): ?>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/ver/<?=($DteIntercambio->codigo-1)?>" title="Ver intercambio N° <?=($DteIntercambio->codigo-1)?>">
            Anterior
        </a>
    </li>
<?php endif; ?>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/ver/<?=($DteIntercambio->codigo+1)?>" title="Ver intercambio N° <?=($DteIntercambio->codigo+1)?>">
            Siguiente
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/listar" title="Volver a la bandeja de intercambio entre contribuyentes">
            Volver a bandeja intercambio
        </a>
    </li>
</ul>

<h1>Intercambio N° <?=$DteIntercambio->codigo?></h1>
<p>Esta es la página del intercambio N° <?=$DteIntercambio->codigo?> de la empresa <?=$Emisor->razon_social?>.</p>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
    }
});
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#email" aria-controls="email" role="tab" data-toggle="tab">Email recibido y PDF</a></li>
        <li role="presentation"><a href="#documentos" aria-controls="documentos" role="tab" data-toggle="tab">Recepción y acuse de recibo</a></li>
        <li role="presentation"><a href="#avanzado" aria-controls="avanzado" role="tab" data-toggle="tab">Avanzado</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="email">
<?php
$de = $DteIntercambio->de;
if ($DteIntercambio->de!=$DteIntercambio->responder_a)
    $de .= '<br/><span>'.$DteIntercambio->responder_a.'</span>';
new \sowerphp\general\View_Helper_Table([
    ['Recibido', 'De', 'Emisor', 'Firma', 'Documentos', 'Estado', 'Usuario'],
    [$DteIntercambio->fecha_hora_email, $de, $DteIntercambio->getEmisor()->razon_social, $DteIntercambio->fecha_hora_firma, num($DteIntercambio->documentos), $DteIntercambio->getEstado()->estado, $DteIntercambio->getUsuario()->usuario],
]);
?>
<p><strong>Asunto</strong>: <?=$DteIntercambio->asunto?></p>
<p><?=str_replace("\n", '<br/>', strip_tags(base64_decode($DteIntercambio->mensaje)))?></p>
<?php if ($DteIntercambio->mensaje_html) : ?>
<a class="btn btn-default btn-lg btn-block" href="javascript:__.popup('<?=$_base?>/dte/dte_intercambios/html/<?=$DteIntercambio->codigo?>', 800, 600)" role="button">
    <span class="fa fa-html5" style="font-size:24px"></span>
    Ver mensaje del correo electrónico del intercambio
</a>
<br/>
<?php endif; ?>
<div class="row">
    <div class="col-md-4">
        <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/pdf/<?=$DteIntercambio->codigo?>" role="button">
            <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
            Descargar PDF del intercambio
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/xml/<?=$DteIntercambio->codigo?>" role="button">
            <span class="fa fa-file-code-o" style="font-size:24px"></span>
            Descargar XML del intercambio
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/resultados_xml/<?=$DteIntercambio->codigo?>" role="button">
            <span class="fa fa-file-code-o" style="font-size:24px"></span>
            Descargar XML de resultados
        </a>
    </div>
</div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO DOCUMENTOS -->
<div role="tabpanel" class="tab-pane" id="documentos">
<div class="row" style="margin-bottom:1em">
    <div class="col-sm-8">
        <p>Aquí podrá generar y enviar la respuesta para los documentos que <?=$DteIntercambio->getEmisor()->razon_social?> envió a <?=$Emisor->razon_social?>.</p>
    </div>
    <div class="col-sm-4 text-right">
        <a class="btn btn-success btn-lg" href="#" onclick="intercambio_aceptar(); return false" role="button">
            Aceptar
        </a>
        <a class="btn btn-danger btn-lg" href="#" onclick="intercambio_rechazar(); return false" role="button">
            Rechazar
        </a>
    </div>
</div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_intercambios/responder/'.$DteIntercambio->codigo, 'onsubmit'=>'Form.check() && Form.checkSend()']);
$f->setColsLabel(3);
echo '<div class="row">',"\n";
echo '<div class="col-md-6">',"\n";
echo $f->input([
    'name' => 'NmbContacto',
    'label' => 'Contacto',
    'value' => substr($_Auth->User->nombre, 0, 40),
    'attr' => 'maxlength="40"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'MailContacto',
    'label' => 'Correo',
    'value' => substr($_Auth->User->email, 0, 80),
    'attr' => 'maxlength="80"',
    'check' => 'notempty email',
]);
echo $f->input([
    'name' => 'Recinto',
    'label' => 'Recinto',
    'value' => substr($Emisor->direccion.', '.$Emisor->getComuna()->comuna, 0, 80),
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
    'help' => 'Lugar donde se recibieron los productos o prestaron los servicios',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal',
    'label' => 'Sucursal',
    'options' => $Emisor->getSucursales(),
    'help' => 'Sucursal a la que corresponden los documentos',
]);
echo '</div>',"\n";
echo '<div class="col-md-6">',"\n";
echo $f->input([
    'name' => 'responder_a',
    'label' => 'Enviar a',
    'value' => $DteIntercambio->getEmisor()->config_email_intercambio_user ? $DteIntercambio->getEmisor()->config_email_intercambio_user : $DteIntercambio->de,
    'check' => 'notempty email',
]);
$estado_enviodte = $EnvioDte->getEstadoValidacion(['RutReceptor'=>$Emisor->rut.'-'.$Emisor->dv]);
echo $f->input([
    'type' => 'select',
    'name' => 'EstadoRecepEnv',
    'label' => 'Estado',
    'options' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'],
    'value' => $estado_enviodte,
    'check' => 'notempty',
    'attr' => 'onchange="document.getElementById(\'RecepEnvGlosaField\').value=this.selectedOptions[0].textContent"'
]);
echo $f->input([
    'name' => 'RecepEnvGlosa',
    'label' => 'Glosa',
    'value' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$estado_enviodte],
    'check' => 'notempty',
    'attr' => 'maxlength="256"',
    'help' => 'Detalles del estado del envío (sobre todo si se está rechazando)',
]);
echo $f->input([
    'name' => 'periodo',
    'label' => 'Período',
    'value' => date('Ym'),
    'check' => 'notempty integer',
    'help' => 'Período del libro en que se asignará el documento.',
    //'attr' => 'readonly="readonly"',
]);
echo '</div>',"\n";
echo '</div>',"\n";

// Recepción de envío
$RecepcionDTE = [];
foreach ($Documentos as $Dte) {
    $DteRecibido = new \website\Dte\Model_DteRecibido(substr($Dte->getEmisor(), 0, -2), $Dte->getTipo(), $Dte->getFolio(), (int)$Dte->getCertificacion());
    // si el enviodte no está recibido no se reciben los documentos
    if ($estado_enviodte) {
        $estado = 99;
    }
    // si ya está recibido el documento se marca con estado repetido
    else if ($DteRecibido->exists()) {
        $estado = 4;
    }
    // si no está recibido se trata de determinar su estado
    else {
        $estado = $Dte->getEstadoValidacion([
            'RUTEmisor' => $DteIntercambio->getEmisor()->rut.'-'.$DteIntercambio->getEmisor()->dv,
            'RUTRecep'=>$Emisor->rut.'-'.$Emisor->dv
        ]);
    }
    $RecepcionDTE[] = [
        'TipoDTE' => $Dte->getTipo(),
        'Folio' => $Dte->getFolio(),
        'FchEmis' => $Dte->getFechaEmision(),
        'RUTEmisor' => $Dte->getEmisor(),
        'RUTRecep' => $Dte->getReceptor(),
        'MntTotal' => $Dte->getMontoTotal(),
        'EstadoRecepDTE' => $estado,
        'RecepDTEGlosa' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['documento'][$estado],
        'acuse' => (int)(bool)!$estado,
        'acciones' => '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/verificar_datos/'.$Dte->getReceptor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'/'.$Dte->getFechaEmision().'/'.$Dte->getMontoTotal().'/'.$Dte->getEmisor().'\', 750, 550)" title="Verificar datos del documento en la web del SII"><span class="fa fa-search btn btn-default"></span></a> <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$DteIntercambio->codigo.'/0/'.$Dte->getEmisor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'" title="Ver PDF del documento"><span class="fa fa-file-pdf-o btn btn-default"></span></a>',
    ];
}
$f->setStyle(false);
echo $f->input([
    'type' => 'table',
    'id' => 'documentos',
    'label' => 'Documentos',
    'titles' => ['DTE', 'Folio', 'Total', 'Estado', 'Glosa', 'Acuse', 'Acciones'],
    'inputs' => [
        ['name'=>'TipoDTE', 'attr'=>'readonly="readonly" size="3"'],
        ['name'=>'Folio', 'attr'=>'readonly="readonly" size="10"'],
        ['name'=>'FchEmis', 'type'=>'hidden'],
        ['name'=>'RUTEmisor', 'type'=>'hidden'],
        ['name'=>'RUTRecep', 'type'=>'hidden'],
        ['name'=>'MntTotal', 'attr'=>'readonly="readonly" size="10"'],
        ['name'=>'EstadoRecepDTE', 'type'=>'select', 'options'=>\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['documento'], 'attr'=>'style="width:12em" onchange="this.parentNode.parentNode.parentNode.childNodes[7].firstChild.firstChild.value=this.selectedOptions[0].textContent"'],
        ['name'=>'RecepDTEGlosa'],
        ['name'=>'acuse', 'type'=>'select', 'options'=>[1=>'Si', 0=>'No'], 'attr'=>'style="width:5em"'],
        ['type'=>'div', 'name'=>'acciones'],
    ],
    'values' => $RecepcionDTE,
]);
echo '<p>Si el estado es diferente a "DTE Recibido OK" entonces el documento será rechazado (no hay aceptado con reparos). Aquellos documentos con acuse de recibo serán agregados a los documentos recibidos de ',$Emisor->razon_social,'</p>',"\n";
echo '<div class="text-center">';
echo $f->input([
    'type' => 'submit',
    'name' => 'submit',
    'id' => 'btnRespuesta',
    'value' => 'Generar y enviar respuesta del intercambio',
]);
echo '</div>';
echo $f->end(false);
?>
</div>
<!-- FIN DOCUMENTOS -->

<!-- INICIO AVANZADO -->
<div role="tabpanel" class="tab-pane" id="avanzado">

<?php
if ($estado_enviodte==1) {
    debug(implode("\n\n", \sasco\LibreDTE\Log::readAll()));
    echo '<hr/>';
}
?>
<a class="btn btn-danger btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/eliminar/<?=$DteIntercambio->codigo?>" role="button" title="Eliminar intercambio" onclick="return Form.checkSend('¿Confirmar la eliminación del intercambio?')">
    Eliminar archivo EnvioDTE de intercambio
</a>
</div>
<!-- FIN AVANZADO -->

    </div>
</div>
