<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_emitidos/listar" title="Volver a los documentos emitidos">
            Volver a documentos emitidos
        </a>
    </li>
</ul>

<h1>Documento T<?=$DteEmitido->dte?>F<?=$DteEmitido->folio?></h1>
<p>Esta es la página del documento <?=$DteEmitido->getTipo()->tipo?> (<?=$DteEmitido->dte?>) folio número <?=$DteEmitido->folio?> de la empresa <?=$Emisor->razon_social?> emitido a <?=$Receptor->razon_social?> (<?=$Receptor->rut.'-'.$Receptor->dv?>).</p>

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
        <li role="presentation"><a href="#pdf" aria-controls="pdf" role="tab" data-toggle="tab">PDF</a></li>
        <li role="presentation"><a href="#email" aria-controls="email" role="tab" data-toggle="tab">Enviar por email</a></li>
        <li role="presentation"><a href="#intercambio" aria-controls="intercambio" role="tab" data-toggle="tab">Resultado intercambio</a></li>
<?php if ($DteEmitido->getTipo()->permiteCobro()): ?>
        <li role="presentation"><a href="#pagar" aria-controls="pagar" role="tab" data-toggle="tab">Pagar</a></li>
<?php endif; ?>
        <li role="presentation"><a href="#cobranza" aria-controls="cobranza" role="tab" data-toggle="tab">Cobranza</a></li>
        <li role="presentation"><a href="#referencias" aria-controls="referencias" role="tab" data-toggle="tab">Referencias</a></li>
<?php if ($DteEmitido->getTipo()->cedible) : ?>
        <li role="presentation"><a href="#cesion" aria-controls="cesion" role="tab" data-toggle="tab">Cesión</a></li>
<?php endif; ?>
        <li role="presentation"><a href="#avanzado" aria-controls="avanzado" role="tab" data-toggle="tab">Avanzado</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
    <div class="row">
        <div class="col-md-<?=$enviar_sii?9:12?>">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Documento', 'Folio', 'Fecha', 'Receptor', 'Exento', 'Neto', 'IVA', 'Total'],
    [$DteEmitido->getTipo()->tipo, $DteEmitido->folio, \sowerphp\general\Utility_Date::format($DteEmitido->fecha), $Receptor->razon_social, num($DteEmitido->exento), num($DteEmitido->neto), num($DteEmitido->iva), num($DteEmitido->total)],
]);
?>
            <div class="row">
                <div class="col-md-4">
                    <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/pdf/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$Emisor->config_pdf_dte_cedible?>" role="button">
                        <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
                        Descargar PDF
                    </a>
                </div>
                <div class="col-md-4">
                    <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/xml/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
                        <span class="fa fa-file-code-o" style="font-size:24px"></span>
                        Descargar XML
                    </a>
                </div>
                <div class="col-md-4">
                    <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/json/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
                        <span class="fa fa-file-code-o" style="font-size:24px"></span>
                        Descargar JSON
                    </a>
                </div>
            </div>
        </div>
<?php if ($enviar_sii) : ?>
        <div class="col-md-3 center bg-info">
            <span class="lead">Track ID SII: <?=$DteEmitido->track_id?></span>
            <p><strong><?=$DteEmitido->revision_estado?></strong></p>
            <p><?=$DteEmitido->revision_detalle?></p>
<?php if ($DteEmitido->track_id) : ?>
            <p>
                <a class="btn btn-info<?=$DteEmitido->track_id==-1?' disabled':''?>" href="<?=$_base?>/dte/dte_emitidos/actualizar_estado/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">Actualizar estado</a><br/>
                <span style="font-size:0.8em">
<?php if (!$Emisor->config_sii_estado_dte_webservice and $DteEmitido->track_id!=-1) : ?>
                    <a href="<?=$_base?>/dte/dte_emitidos/solicitar_revision/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Solicitar nueva revisión del documento por correo electrónico al SII">solicitar nueva revisión</a>
                    <br/>
<?php endif; ?>
<?php if ($DteEmitido->track_id!=-1) : ?>
                    <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/estado_envio/<?=$DteEmitido->track_id?>', 750, 550)" title="Ver el estado del envío en la web del SII">ver estado envío en SII</a><br/>
<?php endif; ?>
                    <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/verificar_datos/<?=$DteEmitido->getReceptor()->getRUT()?>/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$DteEmitido->fecha?>/<?=$DteEmitido->total?>', 750, 550)" title="Verificar datos del documento en la web del SII">verificar documento en SII</a>
<?php if ($DteEmitido->getEstado()=='R' or $DteEmitido->track_id==-1) : ?>
                    <br/>
                    <a href="<?=$_base?>/dte/dte_emitidos/eliminar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Eliminar documento" onclick="return Form.checkSend('¿Confirmar la eliminación del DTE?')">eliminar documento</a>
<?php endif; ?>
                </span>
            </p>
<?php else: ?>
            <p>
                <a class="btn btn-info" href="<?=$_base?>/dte/dte_emitidos/enviar_sii/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">Enviar documento al SII</a>
                <br/>
                <span style="font-size:0.8em">
                    <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/verificar_datos/<?=$DteEmitido->getReceptor()->getRUT()?>/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$DteEmitido->fecha?>/<?=$DteEmitido->total?>', 750, 550)" title="Verificar datos del documento en la web del SII">verificar documento en SII</a><br/>
                    <a href="<?=$_base?>/dte/dte_emitidos/eliminar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Eliminar documento" onclick="return Form.checkSend('¿Confirmar la eliminación del DTE?')">eliminar documento</a>
                </span>
            </p>
<?php endif; ?>
        </div>
<?php endif; ?>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO PDF -->
<div role="tabpanel" class="tab-pane" id="pdf">
<?php
$pdf_publico = $_url.'/dte/dte_emitidos/pdf/'.$DteEmitido->dte.'/'.$DteEmitido->folio.'/0/'.$Emisor->rut.'/'.$DteEmitido->fecha.'/'.$DteEmitido->total;
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_emitidos/pdf/'.$DteEmitido->dte.'/'.$DteEmitido->folio, 'id'=>'pdfForm', 'onsubmit'=>'Form.check(\'pdfForm\')']);
echo $f->input([
    'type' => 'select',
    'name' => 'papelContinuo',
    'label' => 'Tipo papel',
    'options' => \sasco\LibreDTE\Sii\PDF\Dte::$papel,
    'value' => $Emisor->config_pdf_dte_papel,
    'check' => 'notempty',
]);
echo $f->input(['name'=>'copias_tributarias', 'label'=>'Copias tributarias', 'value'=>(int)$Emisor->config_pdf_copias_tributarias, 'check'=>'notempty integer']);
echo $f->input(['name'=>'copias_cedibles', 'label'=>'Copias cedibles', 'value'=>(int)$Emisor->config_pdf_copias_cedibles, 'check'=>'notempty integer']);
echo $f->end('Descargar PDF');
?>
    <a class="btn btn-primary btn-lg btn-block" href="<?=$pdf_publico?>" role="button">
        Enlace público al PDF del DTE
    </a>
</div>
<!-- FIN PDF -->

<!-- INICIO ENVIAR POR EMAIL -->
<div role="tabpanel" class="tab-pane" id="email">
<?php
$enlace_pagar_dte = $_url.'/pagos/documentos/pagar/'.$DteEmitido->dte.'/'.$DteEmitido->folio.'/'.$Emisor->rut.'/'.$DteEmitido->fecha.'/'.$DteEmitido->total;
if ($emails) {
    $asunto = 'EnvioDTE: '.num($Emisor->rut).'-'.$Emisor->dv.' - '.$DteEmitido->getTipo()->tipo.' N° '.$DteEmitido->folio;
    $mensaje = $Receptor->razon_social.','."\n\n";
    $mensaje .= 'Se adjunta '.$DteEmitido->getTipo()->tipo.' N° '.$DteEmitido->folio.' del día '.\sowerphp\general\Utility_Date::format($DteEmitido->fecha).' por un monto total de $'.num($DteEmitido->total).'.-'."\n\n";
    if ($Emisor->config_pagos_habilitado and $DteEmitido->getTipo()->operacion=='S') {
        $mensaje .= 'Enlace pago en línea: '.$enlace_pagar_dte."\n\n";
    } else {
        $mensaje .= 'Enlace directo: '.$pdf_publico."\n\n";
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
        if ($k=='Email intercambio')
            $checked = [$e];
    }
    echo $f->begin(['action'=>$_base.'/dte/dte_emitidos/enviar_email/'.$DteEmitido->dte.'/'.$DteEmitido->folio, 'id'=>'emailForm', 'onsubmit'=>'Form.check(\'emailForm\')']);
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
    echo $f->input(['type'=>'checkbox', 'name'=>'cedible', 'label'=>'¿Copia cedible?', 'checked'=>$Emisor->config_pdf_dte_cedible]);
    echo $f->end('Enviar PDF y XML por email');
} else {
    echo '<p>No hay emails registrados para el receptor ni el documento.</p>',"\n";
}
?>
</div>
<!-- FIN ENVIAR POR EMAIL -->

<!-- INICIO INTERCAMBIO -->
<div role="tabpanel" class="tab-pane" id="intercambio">
<?php
// recibo
echo '<h2>Recibo</h2>',"\n";
$Recibo = $DteEmitido->getIntercambioRecibo();
if ($Recibo) {
    $Sobre = $Recibo->getSobre();
    new \sowerphp\general\View_Helper_Table([
        ['Contacto', 'Teléfono', 'Email', 'Recinto', 'Firma', 'Fecha y hora', 'XML'],
        [
            $Sobre->contacto,
            $Sobre->telefono,
            $Sobre->email,
            $Recibo->recinto,
            $Recibo->firma,
            $Recibo->fecha_hora,
            '<a href="'.$_base.'/dte/dte_intercambio_recibos/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button"><span class="fa fa-file-code-o btn btn-default"></span></a>',
        ],
    ]);
} else {
    echo '<p>No existe recibo para el documento.</p>';
}
// recepcion
echo '<h2>Recepción</h2>',"\n";
$Recepcion = $DteEmitido->getIntercambioRecepcion();
if ($Recepcion) {
    $Sobre = $Recepcion->getSobre();
    new \sowerphp\general\View_Helper_Table([
        ['Contacto', 'Teléfono', 'Email', 'Estado general', 'Estado documento', 'Fecha y hora', 'XML'],
        [
            $Sobre->contacto,
            $Sobre->telefono,
            $Sobre->email,
            $Sobre->estado.': '.$Sobre->glosa,
            $Recepcion->estado.': '.$Recepcion->glosa,
            $Sobre->fecha_hora,
            '<a href="'.$_base.'/dte/dte_intercambio_recepciones/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button"><span class="fa fa-file-code-o btn btn-default"></span></a>',
        ],
    ]);
} else {
    echo '<p>No existe recepción para el documento.</p>';
}
// resultado
echo '<h2>Resultado</h2>',"\n";
$Resultado = $DteEmitido->getIntercambioResultado();
if ($Resultado) {
    $Sobre = $Resultado->getSobre();
    new \sowerphp\general\View_Helper_Table([
        ['Contacto', 'Teléfono', 'Email', 'Estado', 'Fecha y hora', 'XML'],
        [
            $Sobre->contacto,
            $Sobre->telefono,
            $Sobre->email,
            $Resultado->estado.': '.$Resultado->glosa,
            $Sobre->fecha_hora,
            '<a href="'.$_base.'/dte/dte_intercambio_resultados/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button"><span class="fa fa-file-code-o btn btn-default"></span></a>',
        ],
    ]);
} else {
    echo '<p>No existe resultado para el documento.</p>';
}
?>
</div>
<!-- FIN INTERCAMBIO -->

<?php if ($DteEmitido->getTipo()->permiteCobro()): ?>
<!-- INICIO PAGAR -->
<div role="tabpanel" class="tab-pane" id="pagar">
<?php if ($Emisor->config_pagos_habilitado) : ?>
<?php if (!$Cobro->pagado) : ?>
<div class="row">
    <div class="col-sm-6">
    <a class="btn btn-success btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/pagar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
            Registrar pago
        </a>
    </div>
    <div class="col-sm-6">
        <a class="btn btn-info btn-lg btn-block" href="<?=$enlace_pagar_dte?>" role="button">
            Enlace público para pagar
        </a>
    </div>
</div>
<?php else: ?>
<p>El documento se encuentra pagado con fecha <?=\sowerphp\general\Utility_Date::format($Cobro->pagado)?> usando el medio de pago <?=$Cobro->getMedioPago()->medio_pago?>.</p>
<?php endif; ?>
<?php else : ?>
<p>No tiene los pagos en línea habilitados, debe al menos <a href="<?=$_base?>/dte/contribuyentes/modificar/<?=$Emisor->rut?>#pagos">configurar un medio de pago</a> primero.</p>
<?php endif; ?>
</div>
<!-- FIN PAGAR -->
<?php endif; ?>

<!-- INICIO COBRANZA -->
<div role="tabpanel" class="tab-pane" id="cobranza">
<?php
$cobranza = $DteEmitido->getCobranza();
if ($cobranza) {
    echo '<p>El documento emitido tiene los siguientes pagos programados asociados.</p>',"\n";
    foreach ($cobranza as &$c) {
        $c[] = '<a href="'.$_base.'/dte/cobranzas/cobranzas/ver/'.$DteEmitido->dte.'/'.$DteEmitido->folio.'/'.$c['fecha'].'" title="Ver pago"><span class="fa fa-search btn btn-default"></span></a>';
        $c['fecha'] = \sowerphp\general\Utility_Date::format($c['fecha']);
        $c['monto'] = num($c['monto']);
        if ($c['pagado']!==null) {
            $c['pagado'] = num($c['pagado']);
        }
        if ($c['modificado']) {
            $c['modificado'] = \sowerphp\general\Utility_Date::format($c['modificado']);
        }
    }
    array_unshift($cobranza, ['Fecha', 'Monto', 'Glosa', 'Pagado', 'Observación', 'Usuario', 'Modificado', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($cobranza);
} else {
    echo '<p>No hay pagos programados para este documento.</p>',"\n";
}
?>
</div>
<!-- FIN COBRANZA -->

<!-- INICIO REFERENCIAS -->
<div role="tabpanel" class="tab-pane" id="referencias">
<?php
if ($referencias) {
    echo '<p>Los siguientes son documentos que hacen referencia a este.</p>',"\n";
    foreach ($referencias as &$r) {
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$r['dte'].'/'.$r['folio'].'" title="Ver documento"><span class="fa fa-search btn btn-default"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$r['dte'].'/'.$r['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
        $r[] = $acciones;
        unset($r['dte']);
    }
    array_unshift($referencias, ['Documento', 'Folio', 'Fecha', 'Referencia', 'Razón', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($referencias);
} else {
    echo '<p>No hay documentos que referencien a este.</p>',"\n";
}
?>
<div class="row">
<?php if (!empty($referencia)) : ?>
    <div class="col-md-<?=(!empty($referencia)?6:12)?>">
        <a class="btn btn-<?=$referencia['color']?> btn-lg btn-block" href="<?=$_base?>/dte/documentos/emitir/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$referencia['dte']?>/<?=$referencia['codigo']?>/<?=urlencode($referencia['razon'])?>" role="button">
            <?=$referencia['titulo']?>
        </a>
    </div>
<?php endif; ?>
    <div class="col-md-<?=(!empty($referencia)?6:12)?>">
        <a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/documentos/emitir/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
            Crear referencia
        </a>
    </div>
</div>
</div>
<!-- FIN REFERENCIAS -->

<?php if ($DteEmitido->getTipo()->cedible) : ?>
<!-- INICIO CESIÓN -->
<div role="tabpanel" class="tab-pane" id="cesion">
<?php if ($DteEmitido->cesion_track_id) : ?>
<div class="bg-info lead center" style="padding:0.5em">
    Documento tiene track id de cesión: <?=$DteEmitido->cesion_track_id?>
    <br/>
    <small><a href="<?=$_base?>/dte/dte_emitidos/cesion_xml/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>">Descargar AEC</a></small>
</div>
<?php
else :
echo $f->begin([
    'action' => $_base.'/dte/dte_emitidos/ceder/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
    'id' => 'cesionForm',
    'onsubmit' => 'Form.check(\'cesionForm\') && Form.checkSend(\'¿Está seguro de querer ceder el DTE?\')'
]);
echo $f->input([
    'name' => 'cedente_email',
    'label' => 'Email cedente',
    'check' => 'notempty email',
    'value' => $_Auth->User->email,
]);
echo $f->input([
    'name' => 'cesionario_rut',
    'label' => 'RUT cesionario',
    'check' => 'notempty rut',
]);
echo $f->input([
    'name' => 'cesionario_razon_social',
    'label' => 'Razón social cesionario',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'cesionario_direccion',
    'label' => 'Dirección cesionario',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'cesionario_email',
    'label' => 'Email cesionario',
    'check' => 'notempty email',
]);
echo $f->end('Generar archivo cesión y enviar al SII');
endif;
?>
</div>
<!-- FIN CESIÓN -->
<?php endif; ?>

<!-- INICIO AVANZADO -->
<div role="tabpanel" class="tab-pane" id="avanzado">
<?php
// si es nota de crédito permitir marcar iva como fuera de plazo
if ($DteEmitido->dte == 61) :
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-ban"></i>
        IVA fuera de plazo (no recuperable)
    </div>
    <div class="panel-body">
<?php
echo $f->begin([
    'action' => $_base.'/dte/dte_emitidos/avanzado_iva_fuera_plazo/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
    'id' => 'avanzadoIVAFueraPlazoForm',
    'onsubmit' => 'Form.check(\'avanzadoIVAFueraPlazoForm\')'
]);
echo $f->input([
    'type' => 'select',
    'name' => 'iva_fuera_plazo',
    'label' => '¿Fuera de plazo?',
    'options' => ['No', 'Si'],
    'value' => $DteEmitido->iva_fuera_plazo,
    'help' => 'Marcar el IVA como fuera de plazo (no recuperable, no descuenta IVA débito)',
]);
echo $f->end('Guardar');
?>
    </div>
</div>
<?php endif; ?>
<?php
// si es guía de despacho permitir anular
if ($DteEmitido->dte == 52) :
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-ban"></i>
        Anular DTE
    </div>
    <div class="panel-body">
<?php
echo $f->begin([
    'action' => $_base.'/dte/dte_emitidos/avanzado_anular/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
    'id' => 'avanzadoAnuladoForm',
    'onsubmit' => 'Form.check(\'avanzadoAnuladoForm\')'
]);
echo $f->input([
    'type' => 'select',
    'name' => 'anulado',
    'label' => '¿Anulado?',
    'options' => ['No', 'Si'],
    'value' => $DteEmitido->anulado,
    'help' => 'Marcar el DTE como anulado',
]);
echo $f->end('Guardar');
?>
    </div>
</div>
<?php endif; ?>
<?php
// si es exportación permitir cambiar tipo de cambio
if ($DteEmitido->getDte()->esExportacion()) :
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-dollar"></i>
        Tipo de cambio para valor en pesos (CLP)
    </div>
    <div class="panel-body">
<?php
    echo $f->begin([
        'action' => $_base.'/dte/dte_emitidos/avanzado_tipo_cambio/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
        'id' => 'avanzadoTipoCambioForm',
        'onsubmit' => 'Form.check(\'avanzadoTipoCambioForm\') && Form.checkSend(\'¿Está seguro de querer modificar el tipo de cambio del documento?\')'
    ]);
    echo $f->input([
        'name' => 'tipo_cambio',
        'label' => 'Tipo de cambio',
        'check' => 'notempty real',
        'help' => 'Monto en pesos (CLP) equivalente a 1 '.$DteEmitido->getDte()->getMoneda(),
    ]);
    echo $f->end('Modificar el tipo de cambio');
?>
    </div>
</div>
<?php endif; ?>
<?php if ($DteEmitido->getTipo()->enviar) : ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-send-o"></i>
        Track ID o identificador del envío
    </div>
    <div class="panel-body">
<?php
// permitir cambiar el track id
echo $f->begin([
    'action' => $_base.'/dte/dte_emitidos/avanzado_track_id/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
    'id' => 'avanzadoTrackIdForm',
    'onsubmit' => 'Form.check(\'avanzadoTrackIdForm\') && Form.checkSend(\'¿Está seguro de querer cambiar el Track ID?\n\n¡Perderá el valor actual!\')'
]);
echo $f->input([
    'name' => 'track_id',
    'label' => 'Track ID',
    'value' => $DteEmitido->track_id,
    'check'=>'notempty integer',
    'help' => 'Identificador de envío del XML del DTE al SII',
]);
echo $f->end('Modificar Track ID');
?>
    </div>
</div>
<?php endif; ?>
<p style="margin-top:2em;font-size:0.8em" class="text-right">Documento timbrado el <?=str_replace('T', ' ', $DteEmitido->getDte()->getDatos()['TED']['DD']['TSTED'])?></p>
</div>
<!-- FIN AVANZADO -->

    </div>
</div>
