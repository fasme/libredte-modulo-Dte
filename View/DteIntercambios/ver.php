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
/*function intercambio_aceptar() {
    $('select[name="rcv_accion_codigo[]"]').each(function (i, e) {
        $('select[name="rcv_accion_codigo[]"]').get(i).value = 'ACD';
        $('input[name="rcv_accion_glosa[]"]').get(i).value = 'Acepta contenido del documento';
    });
    $('#btnRespuesta').click();
}*/
function intercambio_recibir() {
    $('select[name="rcv_accion_codigo[]"]').each(function (i, e) {
        $('select[name="rcv_accion_codigo[]"]').get(i).value = 'ERM';
        $('input[name="rcv_accion_glosa[]"]').get(i).value = 'Otorga recibo de mercaderías o servicios';
    });
    $('#btnRespuesta').click();
}
function intercambio_reclamar() {
    $('select[name="rcv_accion_codigo[]"]').each(function (i, e) {
        $('select[name="rcv_accion_codigo[]"]').get(i).value = 'RCD';
        $('input[name="rcv_accion_glosa[]"]').get(i).value = 'Reclamo al contenido del documento';
    });
    $('#btnRespuesta').click();
}
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
            <span class="far fa-file-pdf" style="font-size:24px"></span>
            Descargar PDF del intercambio
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/xml/<?=$DteIntercambio->codigo?>" role="button">
            <span class="far fa-file-code" style="font-size:24px"></span>
            Descargar XML del intercambio
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/resultados_xml/<?=$DteIntercambio->codigo?>" role="button">
            <span class="far fa-file-code" style="font-size:24px"></span>
            Descargar XML de resultados
        </a>
    </div>
</div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO DOCUMENTOS -->
<div role="tabpanel" class="tab-pane" id="documentos">
<div class="row" style="margin-bottom:1em">
    <div class="col-sm-7">
        <p>Aquí podrá generar y enviar la respuesta para los documentos que <?=$DteIntercambio->getEmisor()->razon_social?> envió a <?=$Emisor->razon_social?>.</p>
    </div>
    <div class="col-sm-5 text-right">
        <!--<a class="btn btn-primary btn-lg" href="#" onclick="intercambio_aceptar(); return false" role="button" title="Aceptar el contenido de los documentos (sin acuse de recibo)">
            Aceptar
        </a>-->
        <a class="btn btn-success btn-lg" href="#" onclick="intercambio_recibir(); return false" role="button" title="Generar el acuse de recido para los documentos">
            Recibir
        </a>
        <a class="btn btn-danger btn-lg" href="#" onclick="intercambio_reclamar(); return false" role="button" title="Rechazar los documentos">
            Reclamar
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
    'name' => 'periodo',
    'label' => 'Período',
    'value' => date('Ym'),
    'check' => 'notempty integer',
    'help' => 'Período del libro en que se asignará el documento. Formato: AAAAMM',
    //'attr' => 'readonly="readonly"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal',
    'label' => 'Sucursal',
    'options' => $Emisor->getSucursales(),
    'help' => 'Sucursal a la que corresponden los documentos',
]);
echo '</div>',"\n";
echo '</div>',"\n";

// Recepción de envío
$RecepcionDTE = [];
foreach ($Documentos as $Dte) {
    $DteRecibido = new \website\Dte\Model_DteRecibido(substr($Dte->getEmisor(), 0, -2), $Dte->getTipo(), $Dte->getFolio(), (int)$Dte->getCertificacion());
    $dte_existe = $DteRecibido->exists();
    //$evento = $Dte->getUltimaAccionRCV($Firma);
    //$accion = ($evento and isset(\sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones[$evento['codigo']]))? $evento['codigo'] : '';
    $accion = '';
    $acciones = '';
    if ($dte_existe) {
        $acciones .= '<a href="'.$_base.'/dte/dte_recibidos/modificar/'.$DteRecibido->emisor.'/'.$DteRecibido->dte.'/'.$DteRecibido->folio.'" title="Editar el DTE recibido"><span class="fa fa-edit btn btn-default"></span></a> ';
    }
    $acciones .= '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/verificar_datos/'.$Dte->getReceptor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'/'.$Dte->getFechaEmision().'/'.$Dte->getMontoTotal().'/'.$Dte->getEmisor().'\', 750, 550); return false" title="Verificar datos del documento en la web del SII"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="#" onclick="__.popup(\''.$_base.'/dte/sii/dte_rcv/'.$Dte->getEmisor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'\', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII"><span class="fa fa-eye btn btn-default"></span></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$DteIntercambio->codigo.'/0/'.$Dte->getEmisor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'" title="Ver PDF del documento"><span class="far fa-file-pdf btn btn-default"></span></a>';
    $RecepcionDTE[] = [
        'TipoDTE' => $Dte->getTipo(),
        'Folio' => $Dte->getFolio(),
        'FchEmis' => $Dte->getFechaEmision(),
        'RUTEmisor' => $Dte->getEmisor(),
        'RUTRecep' => $Dte->getReceptor(),
        'MntTotal' => $Dte->getMontoTotal(),
        'rcv_accion_codigo' => $accion,
        'rcv_accion_glosa' => $accion ? \sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones[$accion] : '',
        'recibido' => $dte_existe ? 'Si' : 'No',
        'acciones' => $acciones,
    ];
}
$f->setStyle(false);
echo $f->input([
    'type' => 'table',
    'id' => 'documentos',
    'label' => 'Documentos',
    'titles' => ['DTE', 'Folio', 'Total', 'Estado', 'Glosa', '¿En IC?', 'Acciones'],
    'inputs' => [
        ['name'=>'TipoDTE', 'attr'=>'readonly="readonly" size="3"'],
        ['name'=>'Folio', 'attr'=>'readonly="readonly" size="10"'],
        ['name'=>'FchEmis', 'type'=>'hidden'],
        ['name'=>'RUTEmisor', 'type'=>'hidden'],
        ['name'=>'RUTRecep', 'type'=>'hidden'],
        ['name'=>'MntTotal', 'attr'=>'readonly="readonly" size="10"'],
        ['name'=>'rcv_accion_codigo', 'type'=>'select', 'options'=>[''=>'']+\sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones, 'check' => 'notempty', 'attr'=>'onchange="this.parentNode.parentNode.parentNode.childNodes[7].firstChild.firstChild.value=this.selectedOptions[0].textContent"'],
        ['name'=>'rcv_accion_glosa', 'check' => 'notempty'],
        ['type'=>'div', 'name'=>'recibido'],
        ['type'=>'div', 'name'=>'acciones'],
    ],
    'values' => $RecepcionDTE,
]);
echo '<p>Sólo aquellos documentos <strong>con acuse de recibo serán agregados</strong> a los documentos recibidos de ',$Emisor->razon_social,'. Documentos <strong>aceptados no serán agregados</strong> a los documentos recibidos, pero podrán ser agregados en el futuro si se hace el recibo de mercaderías o servicios. Documentos <strong>con reclamo no serán agregados</strong> a los documentos recibidos y no podrán ser agregados en el futuro ya que serán informados como rechazados al SII.</p>',"\n";
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
if ($_Auth->User->inGroup('soporte')) {
    $tabla = [['DTE', 'Folio', 'Tasa', 'Fecha', 'Sucursal', 'Receptor', 'Razón social receptor', 'Exento', 'Neto', 'IVA', 'Total']];
    foreach ($Documentos as $Dte) {
        $resumen = $Dte->getResumen();
        foreach (['MntExe', 'MntIVA', 'MntNeto', 'MntTotal'] as $monto) {
            if ($resumen[$monto]) {
                $resumen[$monto] = num($resumen[$monto]);
            }
        }
        $tabla[] = $resumen;
    }
    new \sowerphp\general\View_Helper_Table($tabla);
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
