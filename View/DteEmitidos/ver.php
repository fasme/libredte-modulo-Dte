<ul class="nav nav-pills float-right">
<?php if ($Emisor->config_pdf_imprimir) : ?>
    <li class="nav-item">
        <a href="#" onclick="dte_imprimir('<?=$Emisor->config_pdf_imprimir?>', 'dte_emitido', {dte: <?=$DteEmitido->dte?>, folio: <?=$DteEmitido->folio?>}); return false" title="Imprimir el documento (<?=$Emisor->config_pdf_imprimir?>)" accesskey="P" class="nav-link">
            <i class="fa fa-print"></i>
            Imprimir
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>?copiar" title="Crear DTE con los mismos datos de este" class="nav-link">
            <i class="fa fa-copy"></i>
            Copiar
        </a>
    </li>
<?php if (in_array($DteEmitido->dte, array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes))) : ?>
    <li class="nav-item">
        <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/dte_rcv/<?=$Emisor->rut?>-<?=$Emisor->dv?>/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII" class="nav-link">
            <i class="fa fa-book"></i>
            Ver RCV
        </a>
    </li>
<?php endif; ?>
<?php if (\sowerphp\core\Module::loaded('Crm')) : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/crm/clientes/ver/<?=$Receptor->rut?>" title="Ir al CRM de <?=$Receptor->razon_social?>" class="nav-link">
            <i class="fa fa-users"></i>
            CRM
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_emitidos/listar" title="Ir a los documentos emitidos" class="nav-link">
            <i class="fa fa-sign-out-alt"></i>
            Documentos emitidos
        </a>
    </li>
</ul>

<div class="page-header"><h1>Documento T<?=$DteEmitido->dte?>F<?=$DteEmitido->folio?></h1></div>
<p>Esta es la página del documento <?=$DteEmitido->getTipo()->tipo?> (<?=$DteEmitido->dte?>) folio número <?=$DteEmitido->folio?> de la empresa <?=$Emisor->razon_social?> emitido a <?=$Receptor->razon_social?> (<?=$Receptor->rut.'-'.$Receptor->dv?>) en la sucursal <?=$Emisor->getSucursal($DteEmitido->sucursal_sii)->sucursal?>.</p>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('#'+url.split('#')[1]+'-tab').tab('show');
    }
});
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab" id="datos-tab" class="nav-link active" aria-selected="true">Datos básicos</a></li>
        <li class="nav-item"><a href="#pdf" aria-controls="pdf" role="tab" data-toggle="tab" id="pdf-tab" class="nav-link">PDF</a></li>
        <li class="nav-item"><a href="#email" aria-controls="email" role="tab" data-toggle="tab" id="email-tab" class="nav-link">Enviar por email</a></li>
<?php if ($DteEmitido->getTipo()->permiteIntercambio()): ?>
        <li class="nav-item"><a href="#intercambio" aria-controls="intercambio" role="tab" data-toggle="tab" id="intercambio-tab" class="nav-link">Resultado intercambio</a></li>
<?php endif; ?>
<?php if ($DteEmitido->getTipo()->permiteCobro()): ?>
        <li class="nav-item"><a href="#pagar" aria-controls="pagar" role="tab" data-toggle="tab" id="pagar-tab" class="nav-link">Pagar</a></li>
<?php endif; ?>
<?php if ($DteEmitido->getTipo()->operacion=='S'): ?>
        <li class="nav-item"><a href="#cobranza" aria-controls="cobranza" role="tab" data-toggle="tab" id="cobranza-tab" class="nav-link">Cobranza</a></li>
<?php endif; ?>
        <li class="nav-item"><a href="#referencias" aria-controls="referencias" role="tab" data-toggle="tab" id="referencias-tab" class="nav-link">Referencias</a></li>
<?php if ($DteEmitido->getTipo()->cedible) : ?>
        <li class="nav-item"><a href="#cesion" aria-controls="cesion" role="tab" data-toggle="tab" id="cesion-tab" class="nav-link">Cesión</a></li>
<?php endif; ?>
        <li class="nav-item"><a href="#avanzado" aria-controls="avanzado" role="tab" data-toggle="tab" id="avanzado-tab" class="nav-link">Avanzado</a></li>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos" aria-labelledby="datos-tab">
    <div class="row">
        <div class="col-md-<?=$enviar_sii?9:12?>">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Documento', 'Folio', 'Fecha', 'Receptor', 'Exento', 'Neto', 'IVA', 'Total'],
    [$DteEmitido->getTipo()->tipo, $DteEmitido->folio, \sowerphp\general\Utility_Date::format($DteEmitido->fecha), $Receptor->razon_social, num($DteEmitido->exento), num($DteEmitido->neto), num($DteEmitido->iva), num($DteEmitido->total)],
]);
?>
            <div class="row mt-2">
                <div class="col-md-4 mb-2">
                    <a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/pdf/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$Emisor->config_pdf_dte_cedible?>" role="button">
                        <span class="far fa-file-pdf"></span>
                        Descargar PDF
                    </a>
                </div>
                <div class="col-md-4 mb-2">
                    <a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/xml/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
                        <span class="far fa-file-code"></span>
                        Descargar XML
                    </a>
                </div>
                <div class="col-md-4 mb-2">
                    <a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/json/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
                        <span class="far fa-file-code"></span>
                        Descargar JSON
                    </a>
                </div>
            </div>
        </div>
<?php if ($enviar_sii) : ?>
        <div class="col-md-3">
            <div class="card mb-4 bg-light">
                <div class="card-header lead text-center">Track ID SII: <?=$DteEmitido->track_id?></div>
                <div class="card-body text-center">
                    <p><strong><?=$DteEmitido->revision_estado?></strong></p>
                    <p><?=$DteEmitido->revision_detalle?></p>
<?php if ($DteEmitido->track_id) : ?>
                    <p>
                        <a class="btn btn-primary<?=$DteEmitido->track_id==-1?' disabled':''?>" href="<?=$_base?>/dte/dte_emitidos/actualizar_estado/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">Actualizar estado</a><br/>
                        <span style="font-size:0.8em">
<?php if (!$Emisor->config_sii_estado_dte_webservice and $DteEmitido->track_id!=-1) : ?>
                            <a href="<?=$_base?>/dte/dte_emitidos/solicitar_revision/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Solicitar nueva revisión del documento por correo electrónico al SII">solicitar nueva revisión</a>
                            <br/>
<?php endif; ?>
<?php if ($DteEmitido->track_id!=-1) : ?>
                            <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/estado_envio/<?=$DteEmitido->track_id?>', 750, 550)" title="Ver el estado del envío en la web del SII">ver estado envío en SII</a><br/>
<?php endif; ?>
                            <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/verificar_datos/<?=$DteEmitido->getReceptor()->getRUT()?>/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$DteEmitido->fecha?>/<?=$DteEmitido->getTotal()?>', 750, 550)" title="Verificar datos del documento en la web del SII">verificar documento en SII</a>
<?php if (substr($DteEmitido->revision_estado,0,3)=='RFR') : ?>
                            <br/>
                            <a href="<?=$_base?>/dte/dte_emitidos/enviar_sii/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" onclick="return Form.confirm(this, '¿Confirmar el reenvío del DTE al SII?')">reenviar DTE al SII</a>
<?php endif; ?>
<?php if ($DteEmitido->getEstado()=='R' or $DteEmitido->track_id==-1) : ?>
                            <br/>
                            <a href="<?=$_base?>/dte/dte_emitidos/eliminar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Eliminar documento" onclick="return Form.confirm(this, '¿Confirmar la eliminación del DTE?')">eliminar documento</a>
<?php endif; ?>
                        </span>
                    </p>
<?php else: ?>
                    <p>
                        <a class="btn btn-primary" href="<?=$_base?>/dte/dte_emitidos/enviar_sii/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">Enviar documento al SII</a>
                        <br/>
                        <span style="font-size:0.8em">
                            <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/verificar_datos/<?=$DteEmitido->getReceptor()->getRUT()?>/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$DteEmitido->fecha?>/<?=$DteEmitido->getTotal()?>', 750, 550)" title="Verificar datos del documento en la web del SII">verificar documento en SII</a><br/>
                            <a href="<?=$_base?>/dte/dte_emitidos/eliminar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Eliminar documento" onclick="return Form.confirm(this, '¿Confirmar la eliminación del DTE?')">eliminar documento</a>
                        </span>
                    </p>
<?php endif; ?>
                </div>
            </div>
        </div>
<?php endif; ?>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO PDF -->
<div role="tabpanel" class="tab-pane" id="pdf" aria-labelledby="pdf-tab">
<?php
$links = $DteEmitido->getLinks();
$pdf_publico = $links['pdf'];
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_emitidos/pdf/'.$DteEmitido->dte.'/'.$DteEmitido->folio, 'id'=>'pdfForm', 'onsubmit'=>'Form.check(\'pdfForm\')']);
echo $f->input([
    'type' => 'select',
    'name' => 'papelContinuo',
    'label' => 'Tipo papel',
    'options' => \sasco\LibreDTE\Sii\Dte\PDF\Dte::$papel,
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
<div role="tabpanel" class="tab-pane" id="email" aria-labelledby="email-tab">
<?php
$enlace_pagar_dte = !empty($links['pagar']) ? $links['pagar'] : null;
$asunto = $DteEmitido->getTipo()->tipo.' N° '.$DteEmitido->folio.' de '.$Emisor->getNombre().' ('.$Emisor->getRUT().')';
if (!$email_html) {
    $mensaje = $Receptor->razon_social.','."\n\n";
    $mensaje .= 'Se adjunta '.$DteEmitido->getTipo()->tipo.' N° '.$DteEmitido->folio.' del día '.\sowerphp\general\Utility_Date::format($DteEmitido->fecha).' por un monto total de $'.num($DteEmitido->total).'.-'."\n\n";
    if ($enlace_pagar_dte) {
        if (!$Cobro->pagado) {
            $mensaje .= 'Enlace pago en línea: '.$enlace_pagar_dte."\n\n";
        } else {
            $mensaje .= 'El documento se encuentra pagado con fecha '.\sowerphp\general\Utility_Date::format($Cobro->pagado).' usando el medio de pago '.$Cobro->getMedioPago()->getNombre()."\n\n";
        }
    } else {
        $mensaje .= 'Enlace directo: '.$pdf_publico."\n\n";
    }
    $mensaje .= 'Saluda atentamente,'."\n\n";
    $mensaje .= '-- '."\n";
    $mensaje .= $Emisor->getNombre()."\n";
    $mensaje .= $Emisor->giro."\n";
    $contacto = [];
    if (!empty($Emisor->telefono)) {
        $contacto[] = $Emisor->telefono;
    }
    if (!empty($Emisor->email)) {
        $contacto[] = $Emisor->email;
    }
    if ($Emisor->config_extra_web) {
        $contacto[] = $Emisor->config_extra_web;
    }
    if ($contacto) {
        $mensaje .= implode(' - ', $contacto)."\n";
    }
    $mensaje .= $Emisor->direccion.', '.$Emisor->getComuna()->comuna."\n";
} else $mensaje = '';
echo $f->begin(['action'=>$_base.'/dte/dte_emitidos/enviar_email/'.$DteEmitido->dte.'/'.$DteEmitido->folio, 'id'=>'emailForm', 'onsubmit'=>'Form.check(\'emailForm\')']);
if ($emails) {
    $table = [];
    $checked = [];
    foreach ($emails as $k => $e) {
        $table[] = [$e, $k];
        if ($k=='Email intercambio') {
            $checked = [$e];
        }
    }
    echo $f->input([
        'type' => 'tablecheck',
        'name' => 'emails',
        'label' => 'Para',
        'titles' => ['Email', 'Origen'],
        'table' => $table,
        'checked' => $checked,
        'help' => 'Seleccionar emails a los que se enviará el documento',
    ]);
}
echo $f->input(['name'=>'para_extra', 'label'=>'Para (extra)', 'check'=>'emails', 'placeholder'=>'correo@empresa.cl, otro@empresa.cl']);
echo $f->input(['name'=>'asunto', 'label'=>'Asunto', 'value'=>$asunto, 'check'=>'notempty']);
echo $f->input([
    'type' => 'textarea',
    'name' => 'mensaje',
    'label' => 'Mensaje',
    'value' => $mensaje,
    'rows' => !$email_html?10:4,
    'check' => !$email_html?'notempty':'',
    'help' => $email_html?('<a href="#" onclick="__.popup(\''.$_base.'/dte/dte_emitidos/email_html/'.$DteEmitido->dte.'/'.$DteEmitido->folio.'\', 750, 550); return false">Correo por defecto es HTML</a>, si agrega un mensaje acá será añadido al campo {msg_txt} del mensaje HTML'):'',
]);
echo $f->input(['type'=>'checkbox', 'name'=>'cedible', 'label'=>'¿Copia cedible?', 'checked'=>$Emisor->config_pdf_dte_cedible]);
echo $f->end('Enviar PDF y XML por email');
$email_enviados = $DteEmitido->getEmailEnviadosResumen();
if ($email_enviados) {
    echo '<hr/>';
    foreach ($email_enviados as &$e) {
        $e['enviados'] = num($e['enviados']);
        $e['primer_envio'] = \sowerphp\general\Utility_Date::format($e['primer_envio'], 'H:i \e\l d/m/Y');
        $e['ultimo_envio'] = \sowerphp\general\Utility_Date::format($e['ultimo_envio'], 'H:i \e\l d/m/Y');
    }
    array_unshift($email_enviados, ['Email', 'Cantidad de envíos', 'Primer envío', 'Último envío']);
    new \sowerphp\general\View_Helper_Table($email_enviados);
}
?>
</div>
<!-- FIN ENVIAR POR EMAIL -->

<?php if ($DteEmitido->getTipo()->permiteIntercambio()): ?>
<!-- INICIO INTERCAMBIO -->
<div role="tabpanel" class="tab-pane" id="intercambio" aria-labelledby="intercambio-tab">
<?php if (in_array($DteEmitido->dte, array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes))) : ?>
<?php
$color = [
    '' => 'light',
    'A' => 'primary',
    'C' => 'success',
    'P' => 'warning',
    'R' => 'danger',
][$DteEmitido->receptor_evento];
?>
<a href="#" onclick="__.popup('<?=$_base?>/dte/sii/dte_rcv/<?=$Emisor->rut?>-<?=$Emisor->dv?>/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII" class="btn btn-<?=$color?> btn-lg btn-block">
    <?=($DteEmitido->receptor_evento?\sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos[$DteEmitido->receptor_evento]:'Sin evento registrado')?><br/>
    <small>(ver datos en el registro de compra/venta en el SII)</small>
</a>
<hr/>
<?php endif; ?>
   <div class="card mb-4">
        <div class="card-header">Recibo</div>
        <div class="card-body">
<?php
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
            '<a href="'.$_base.'/dte/dte_intercambio_recibos/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button" class="btn btn-primary"><i class="far fa-file-code fa-fw"></i></a>',
        ],
    ]);
} else {
    echo '<p>No existe recibo para el documento.</p>';
}
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Recepción</div>
        <div class="card-body">
<?php
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
            '<a href="'.$_base.'/dte/dte_intercambio_recepciones/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button" class="btn btn-primary"><i class="far fa-file-code fa-fw"></i></a>',
        ],
    ]);
} else {
    echo '<p>No existe recepción para el documento.</p>';
}
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Resultado</div>
        <div class="card-body">
<?php
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
            '<a href="'.$_base.'/dte/dte_intercambio_resultados/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button" class="btn btn-primary"><i class="far fa-file-code fa-fw"></i></a>',
        ],
    ]);
} else {
    echo '<p>No existe resultado para el documento.</p>';
}
?>
        </div>
    </div>
</div>
<!-- FIN INTERCAMBIO -->
<?php endif; ?>

<?php if ($DteEmitido->getTipo()->permiteCobro()): ?>
<!-- INICIO PAGAR -->
<div role="tabpanel" class="tab-pane" id="pagar" aria-labelledby="pagar-tab">
<?php if ($Emisor->config_pagos_habilitado) : ?>
<?php if (!$Cobro->pagado) : ?>
<div class="row">
    <div class="col-sm-6 mb-2">
        <a class="btn btn-success btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/pagar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
            Registrar pago
        </a>
    </div>
    <div class="col-sm-6 mb-2">
        <a class="btn btn-info btn-lg btn-block" href="<?=$enlace_pagar_dte?>" role="button">
            Enlace público para pagar
        </a>
    </div>
</div>
<?php else: ?>
<p>El documento se encuentra pagado con fecha <strong><?=\sowerphp\general\Utility_Date::format($Cobro->pagado)?></strong> usando el medio de pago <strong><?=$Cobro->getMedioPago()->getNombre()?></strong>.</p>
<?php
if ($Cobro->datos) {
    $datos = $Cobro->getDatosNormalizados();
    if ($datos) {
        echo '<hr/><table class="table table-striped"><tbody>';
        foreach($datos as $dato => $valor) {
            echo '<tr><th>',$dato,'</th><td>',$valor,'</td></tr>';
        }
        echo '</tbody></table>',"\n";
    }
}
?>
<?php if (!empty($Emisor->config_api_servicios->pagos_notificar->url)) : ?>
<hr/>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/pagos/cobros/notificar_pago/<?=$Cobro->codigo?>" role="button" title="Notificar pago a <?=$Emisor->config_api_servicios->pagos_notificar->url?>">
    Notificar pago a servicio web del emisor
</a>
<?php endif; ?>
<?php endif; ?>
<?php else : ?>
<p>No tiene los pagos en línea habilitados, debe al menos <a href="<?=$_base?>/dte/contribuyentes/modificar/<?=$Emisor->rut?>#pagos">configurar un medio de pago</a> primero.</p>
<?php endif; ?>
</div>
<!-- FIN PAGAR -->
<?php endif; ?>

<?php if ($DteEmitido->getTipo()->operacion=='S'): ?>
<!-- INICIO COBRANZA -->
<div role="tabpanel" class="tab-pane" id="cobranza" aria-labelledby="cobranza-tab">
<?php
$cobranza = $DteEmitido->getCobranza();
if ($cobranza) {
    echo '<p>El documento emitido tiene los siguientes pagos programados asociados.</p>',"\n";
    foreach ($cobranza as &$c) {
        $c[] = '<a href="'.$_base.'/dte/cobranzas/cobranzas/ver/'.$DteEmitido->dte.'/'.$DteEmitido->folio.'/'.$c['fecha'].'" title="Ver pago" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
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
    echo '<p>No hay pagos programados por ventas a crédito para este documento.</p>',"\n";
}
?>
</div>
<!-- FIN COBRANZA -->
<?php endif; ?>

<!-- INICIO REFERENCIAS -->
<div role="tabpanel" class="tab-pane" id="referencias" aria-labelledby="referencias-tab">
    <div class="card mb-4">
        <div class="card-header">Documentos referenciados</div>
        <div class="card-body">
<?php
// referencias que este documento hace a otros
if ($referenciados) {
    array_unshift($referenciados, ['#', 'DTE', 'Ind. Global', 'Folio', 'RUT otro cont.', 'Fecha', 'Código ref.', 'Razón ref.', 'Vendedor', 'Caja']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setShowEmptyCols(false);
    echo $t->generate($referenciados);
} else {
    echo '<p>Este documento no hace referencia a otros.</p>',"\n";
}
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Documentos que referencian este</div>
        <div class="card-body">
<?php
// referencias que tienen otros documentos a este
if ($referencias) {
    foreach ($referencias as &$r) {
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$r['dte'].'/'.$r['folio'].'" title="Ver documento" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$r['dte'].'/'.$r['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento" class="btn btn-primary"><i class="far fa-file-pdf fa-fw"></i></a>';
        $r[] = $acciones;
        unset($r['dte']);
    }
    array_unshift($referencias, ['Documento', 'Folio', 'Fecha', 'Referencia', 'Razón', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($referencias);
} else {
    echo '<p>No hay otros documentos que hacen referencia a este.</p>',"\n";
}
?>
        </div>
    </div>
<div class="row">
<?php if (!empty($referencia)) : ?>
    <div class="col-md-<?=(!empty($referencia)?6:12)?> mb-2">
        <a class="btn btn-<?=$referencia['color']?> btn-lg btn-block" href="<?=$_base?>/dte/documentos/emitir/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$referencia['dte']?>/<?=$referencia['codigo']?>/<?=urlencode($referencia['razon'])?>" role="button">
            <?=$referencia['titulo']?>
        </a>
    </div>
<?php endif; ?>
    <div class="col-md-<?=(!empty($referencia)?6:12)?> mb-2">
        <a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/documentos/emitir/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
            Crear referencia
        </a>
    </div>
</div>
</div>
<!-- FIN REFERENCIAS -->

<?php if ($DteEmitido->getTipo()->cedible) : ?>
<!-- INICIO CESIÓN -->
<div role="tabpanel" class="tab-pane" id="cesion" aria-labelledby="cesion-tab">
<?php if ($DteEmitido->cesion_track_id) : ?>
<div class="row">
    <div class="col-md-9">
        <p class="lead">Documento tributario electrónico se encuentra cedido según la siguiente información:</p>
<?php
new \sowerphp\general\View_Helper_Table([
    ['RUT', 'Cesionario', 'Dirección', 'Email', 'Fecha cesión'],
    [
        $DteEmitido->getDatosCesion()['Cesionario']['RUT'],
        $DteEmitido->getDatosCesion()['Cesionario']['RazonSocial'],
        $DteEmitido->getDatosCesion()['Cesionario']['Direccion'],
        $DteEmitido->getDatosCesion()['Cesionario']['eMail'],
        \sowerphp\general\Utility_Date::format($DteEmitido->getDatosCesion()['TmstCesion']),
    ],
]);
?>
        <div class="card mb-4">
            <div class="card-body"><?=$DteEmitido->getDatosCesion()['Cedente']['DeclaracionJurada']?></div>
        </div>
        <a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/cesion_xml/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
            <span class="far fa-file-code"></span>
            Descargar Archivo Electrónico de Cesión (AEC)
        </a>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 bg-light">
            <div class="card-header lead text-center">Track ID SII: <?=$DteEmitido->cesion_track_id?></div>
            <div class="card-body small text-center">
                <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/cesion_estado_envio/<?=$DteEmitido->cesion_track_id?>', 750, 550)" title="Ver el estado del envío en la web del SII">ver estado envío en SII</a><br/>
                <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/cesion_certificado/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=\sowerphp\general\Utility_Date::format($DteEmitido->getDatosCesion()['TmstCesion'],'Y-m-d')?>', 750, 550)" title="Obtener el certificado de la cesión del DTE">obtener certificado de cesión</a><br/>
                <a href="https://<?=$servidor_sii?>.sii.cl/rtc/RTC/RTCMenu.html" target="_blank">ir al Registro de Cesión en SII</a>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body lead text-center">
                <a href="http://www.sii.cl/preguntas_frecuentes/catastro/001_012_6407.htm" target="_blank">¿Cómo puedo anular una cesión?</a>
            </div>
        </div>
<?php if ($Emisor->usuarioAutorizado($_Auth->User, 'admin')) : ?>
        <a class="btn btn-danger btn-sm btn-block" href="<?=$_base?>/dte/dte_emitidos/cesion_eliminar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button" onclick="return Form.confirm(this, '¿Está seguro de eliminar la cesión de LibreDTE?\nSi continúa ¡perderá el archivo AEC!')">
            Eliminar cesión
        </a>
<?php endif; ?>
    </div>
</div>
<?php
else :
echo $f->begin([
    'action' => $_base.'/dte/dte_emitidos/ceder/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
    'id' => 'cesionForm',
    'onsubmit' => 'Form.check(\'cesionForm\') && Form.confirm(this, \'¿Está seguro de querer ceder el DTE?\')'
]);
?>
<div class="card mb-4">
    <div class="card-header">Datos del cedente (<?=$Emisor->getNombre()?>)</div>
    <div class="card-body">
<?php
echo $f->input([
    'name' => 'cedente_email',
    'label' => 'Correo contacto',
    'check' => 'notempty email',
    'value' => $_Auth->User->email,
    'help' => 'Correo electrónico del usuario responsable en '.$Emisor->getNombre().' de la cesión que se está realizando',
]);
?>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">Datos del cesionario (ej: la empresa de factoring a quien se cede el DTE)</div>
    <div class="card-body">
<?php
echo $f->input([
    'name' => 'cesionario_rut',
    'label' => 'RUT',
    'check' => 'notempty rut',
    'help' => 'RUT de la empresa a la que se está cediendo el DTE',
]);
echo $f->input([
    'name' => 'cesionario_razon_social',
    'label' => 'Razón social',
    'check' => 'notempty',
    'help' => 'Razón social de la empresa a la que se está cediendo el DTE',
]);
echo $f->input([
    'name' => 'cesionario_direccion',
    'label' => 'Dirección',
    'check' => 'notempty',
    'help' => 'Dirección completa de la empresa a la que se está cediendo el DTE',
]);
echo $f->input([
    'name' => 'cesionario_email',
    'label' => 'Correo contacto',
    'check' => 'notempty email',
    'help' => 'Correo electrónico del contacto en la empresa a la que se está cediendo el DTE',
]);
?>
    </div>
</div>
<?php
echo $f->end('Generar archivo cesión y enviar al SII');
endif;
?>
</div>
<!-- FIN CESIÓN -->
<?php endif; ?>

<!-- INICIO AVANZADO -->
<div role="tabpanel" class="tab-pane" id="avanzado" aria-labelledby="avanzado-tab">
<?php
// si es nota de crédito permitir marcar iva como fuera de plazo
if ($DteEmitido->dte == 61) :
?>
<div class="card mt-4">
    <div class="card-header">
        <i class="fa fa-ban"></i>
        IVA fuera de plazo (no recuperable)
    </div>
    <div class="card-body">
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
<div class="card mt-4">
    <div class="card-header">
        <i class="fa fa-ban"></i>
        Anular DTE
    </div>
    <div class="card-body">
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
// si es exportación permitir cambiar tipo de cambio (sólo si es usuario administrador)
if ($Emisor->usuarioAutorizado($_Auth->User, 'admin') and $DteEmitido->getDte()->esExportacion()) :
?>
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-dollar-sign"></i>
        Tipo de cambio para valor en pesos (CLP)
    </div>
    <div class="card-body">
<?php
    echo $f->begin([
        'action' => $_base.'/dte/dte_emitidos/avanzado_tipo_cambio/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
        'id' => 'avanzadoTipoCambioForm',
        'onsubmit' => 'Form.check(\'avanzadoTipoCambioForm\') && Form.confirm(this, \'¿Está seguro de querer modificar el tipo de cambio del documento?\')'
    ]);
    echo $f->input([
        'name' => 'tipo_cambio',
        'label' => 'Tipo de cambio',
        'check' => 'notempty real',
        'help' => 'Monto en pesos (CLP) equivalente a 1 '.$DteEmitido->getDte()->getMoneda().' del día '.\sowerphp\general\Utility_Date::format($DteEmitido->fecha),
    ]);
    echo $f->end('Modificar el tipo de cambio');
?>
    </div>
</div>
<?php endif; ?>
<?php if ($DteEmitido->getTipo()->enviar) : ?>
<div class="card mt-4">
    <div class="card-header">
        <i class="far fa-paper-plane"></i>
        Track ID o identificador del envío
    </div>
    <div class="card-body">
<?php
// permitir cambiar el track id
echo $f->begin([
    'action' => $_base.'/dte/dte_emitidos/avanzado_track_id/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
    'id' => 'avanzadoTrackIdForm',
    'onsubmit' => 'Form.check(\'avanzadoTrackIdForm\') && Form.confirm(this, \'¿Está seguro de querer cambiar el Track ID?\n\n¡Perderá el valor actual!\')'
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
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-map-marker-alt"></i>
        Cambiar sucursal
    </div>
    <div class="card-body">
<?php
echo $f->begin([
    'action' => $_base.'/dte/dte_emitidos/avanzado_sucursal/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
    'id' => 'avanzadoSucursalForm',
    'onsubmit' => 'Form.check(\'avanzadoSucursalForm\')'
]);
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal',
    'label' => 'Sucursal',
    'options' => $sucursales,
    'value' => $DteEmitido->sucursal_sii,
    'help' => 'El cambio de sucursal sólo afecta al registro de LibreDTE, el DTE (XML y PDF) seguirán con la sucursal originalmente asignada. Si desea un cambio en la sucursal del DTE deberá anular el documento y emitir uno nuevo.',
]);
echo $f->end('Modificar sucursal');
?>
    </div>
</div>
<p style="margin-top:2em;font-size:0.8em" class="text-right">Documento timbrado el <?=str_replace('T', ' ', $DteEmitido->getDte()->getDatos()['TED']['DD']['TSTED'])?></p>
</div>
<!-- FIN AVANZADO -->

    </div>
</div>
