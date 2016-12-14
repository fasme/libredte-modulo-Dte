<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_tmps" title="Volver a los documentos temporales">
            Volver a documentos temporales
        </a>
    </li>
</ul>

<h1>Documento temporal <?=$DteTmp->getFolio()?></h1>
<p>Esta es la página del documento temporal <?=$DteTmp->getTipo()->tipo?> folio <?=$DteTmp->getFolio()?> de la empresa <?=$Emisor->razon_social?> emitido a <?=$Receptor->razon_social?> (<?=$Receptor->rut.'-'.$Receptor->dv?>).</p>

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
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos básicos</a></li>
        <li role="presentation"><a href="#email" aria-controls="email" role="tab" data-toggle="tab">Enviar por email</a></li>
<?php if (\sowerphp\core\Module::loaded('Pagos') and $DteTmp->getDte()->operacion=='S'): ?>
        <li role="presentation"><a href="#pagar" aria-controls="pagar" role="tab" data-toggle="tab">Pagar</a></li>
<?php endif; ?>
        <li role="presentation"><a href="#actualizar_fecha" aria-controls="actualizar_fecha" role="tab" data-toggle="tab">Actualizar fecha</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Documento', 'Folio', 'Fecha', 'Receptor', 'Total'],
    [$DteTmp->getTipo()->tipo, $DteTmp->getFolio(), \sowerphp\general\Utility_Date::format($DteTmp->fecha), $Receptor->razon_social, num($DteTmp->total)],
]);
?>
    <div class="row">
        <div class="col-md-3">
            <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/cotizacion/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <span class="fa fa-dollar" style="font-size:24px"></span>
                Cotización
            </a>
        </div>
        <div class="col-md-3">
            <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/pdf/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
                Previsualización
            </a>
        </div>
        <div class="col-md-3">
            <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/xml/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <span class="fa fa-file-code-o" style="font-size:24px"></span>
                XML sin firmar
            </a>
        </div>
        <div class="col-md-3">
            <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/json/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <span class="fa fa-file-code-o" style="font-size:24px"></span>
                Archivo JSON
            </a>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-md-6">
            <a class="btn btn-success btn-lg btn-block" href="<?=$_base?>/dte/documentos/generar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button" onclick="return Form.checkSend('Confirmar la generación del DTE real')">Generar DTE real</a>
        </div>
        <div class="col-md-6">
            <a class="btn btn-danger btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/eliminar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" title="Eliminar documento" onclick="return Form.checkSend('Confirmar la eliminación del documento temporal')">Eliminar documento</a>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO ENVIAR POR EMAIL -->
<div role="tabpanel" class="tab-pane" id="email">
<?php
$enlace_pagar_cotizacion = $_url.'/pagos/cotizaciones/pagar/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo.'/'.$DteTmp->emisor;
if ($emails) {
    $asunto = 'Documento N° '.$DteTmp->getFolio().' de '.$Emisor->razon_social.' ('.$Emisor->getRUT().')';
    $mensaje = $Receptor->razon_social.','."\n\n";
    $mensaje .= 'Se adjunta documento N° '.$DteTmp->getFolio().' del día '.\sowerphp\general\Utility_Date::format($DteTmp->fecha).' por un monto total de $'.num($DteTmp->total).'.-'."\n\n";
    if ($Emisor->config_pagos_habilitado and $DteTmp->getDte()->operacion=='S') {
        $mensaje .= 'Enlace pago en línea: '.$enlace_pagar_cotizacion."\n\n";
    }
    $mensaje .= 'Saluda atentamente,'."\n\n";
    $mensaje .= '-- '."\n";
    if ($Emisor->config_extra_nombre_fantasia) {
        $mensaje .= $Emisor->config_extra_nombre_fantasia.' ('.$Emisor->razon_social.')'."\n";
    } else {
        $mensaje .= $Emisor->razon_social."\n";
    }
    $mensaje .= $Emisor->giro."\n";
    $contacto = [];
    if (!empty($Emisor->telefono))
        $contacto[] = $Emisor->telefono;
    if (!empty($Emisor->email))
        $contacto[] = $Emisor->email;
    if ($Emisor->config_extra_web)
        $contacto[] = $Emisor->config_extra_web;
    if ($contacto)
        $mensaje .= implode(' - ', $contacto)."\n";
    $mensaje .= $Emisor->direccion.', '.$Emisor->getComuna()->comuna."\n";
    $table = [];
    $checked = [];
    foreach ($emails as $k => $e) {
        $table[] = [$e, $k];
        if (strpos($k, 'Contacto comercial')===0)
            $checked[] = $e;
    }
    $f = new \sowerphp\general\View_Helper_Form();
    echo $f->begin(['action'=>$_base.'/dte/dte_tmps/enviar_email/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo, 'id'=>'emailForm', 'onsubmit'=>'Form.check(\'emailForm\')']);
    echo $f->input([
        'type' => 'tablecheck',
        'name' => 'emails',
        'label' => 'Para',
        'titles' => ['Email', 'Origen'],
        'table' => $table,
        'checked' => $checked,
        'help' => 'Seleccionar emails a los que se enviará el documento',
    ]);
    echo $f->input(['name'=>'asunto', 'label'=>'Asunto', 'value'=>$asunto, 'check'=>'notempty']);
    echo $f->input(['type'=>'textarea', 'name'=>'mensaje', 'label'=>'Mensaje', 'value'=>$mensaje, 'rows'=>10, 'check'=>'notempty']);
    echo $f->input(['type'=>'select', 'name'=>'cotizacion', 'label'=>'Enviar', 'options'=>['Previsualización', 'Cotización'], 'value'=>1]);
    echo $f->end('Enviar PDF por email');
} else {
    echo '<p>No hay emails registrados para el receptor ni el documento.</p>',"\n";
}
?>
</div>
<!-- FIN ENVIAR POR EMAIL -->

<?php if (\sowerphp\core\Module::loaded('Pagos') and $DteTmp->getDte()->operacion=='S'): ?>
<!-- INICIO PAGAR -->
<div role="tabpanel" class="tab-pane" id="pagar">
<?php if ($Emisor->config_pagos_habilitado) : ?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$enlace_pagar_cotizacion?>" role="button">
    Enlace público a la página para el pago de la cotización
</a>
<?php else : ?>
<p>No tiene los pagos en línea habilitados, debe al menos <a href="<?=$_base?>/dte/contribuyentes/modificar/<?=$Emisor->rut?>#pagos">configurar un medio de pago</a> primero.</p>
<?php endif; ?>
</div>
<!-- FIN PAGAR -->
<?php endif; ?>

<!-- INICIO ACTUALIZAR FECHA -->
<div role="tabpanel" class="tab-pane" id="actualizar_fecha">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action' => $_base.'/dte/dte_tmps/actualizar/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo,
    'id' => 'actualizarFechaForm',
    'onsubmit' => 'Form.check(\'actualizarFechaForm\')'
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha',
    'value' => date('Y-m-d'),
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'actualizar_precios',
    'label' => '¿Actualizar precios?',
    'options' => ['No', 'Si'],
    'value' => 1,
    'help' => 'Si el documento tiene items codificados y sus precios no están en pesos (CLP) entonces se pueden actualizar sus valores',
]);
echo $f->end('Actualizar fecha');
?>
</div>
<!-- FIN ACTUALIZAR FECHA -->

    </div>
</div>
