<ul class="nav nav-pills pull-right">
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fas fa-exchange-alt"></span> Tipo transacciones<span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a href="<?=$_base?>/dte/dte_compras/tipo_transacciones_asignar/<?=$Libro->periodo?>">
                     Buscar y asignar
                </a>
            </li>
<?php if (\sowerphp\core\Configure::read('proveedores.api.libredte')) : ?>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_sincronizar_tipo_transacciones/<?=$Libro->periodo?>">
                    Sincronizar con SII
                </a>
            </li>
<?php endif; ?>
        </ul>
    </li>
<?php if (\sowerphp\core\Configure::read('proveedores.api.libredte')) : ?>
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fas fa-university"></span> Ver resumen RC<span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$Libro->periodo?>">
                     Registrados
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$Libro->periodo?>/PENDIENTE">
                    Pendientes
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$Libro->periodo?>/NO_INCLUIR">
                    No incluídos
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$Libro->periodo?>/RECLAMADO">
                    Reclamados
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fa fa-download"></span> Descargar <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a href="<?=$_base?>/dte/dte_compras/descargar_registro_compra/<?=$Libro->periodo?>" title="Descargar CSV con los documentos recibidos que forman el registro de compras">
                     Registro de compras
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/descargar_registro_compra/<?=$Libro->periodo?>/0" title="Descargar CSV con los documentos no electrónicos recibidos que son parte del registro de compras">
                     Documentos no electrónicos
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/descargar_tipo_transacciones/<?=$Libro->periodo?>" title="Descargar CSV con los documentos que tienen tipo de transacción definida">
                    Tipos de transacciones
                </a>
            </li>
        </ul>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_compras" title="Volver a IEC">
            Volver a IEC
        </a>
    </li>
</ul>

<h1>Libro de compras período <?=$Libro->periodo?></h1>
<p>Esta es la página del libro de compras del período <?=$Libro->periodo?> de la empresa <?=$Emisor->razon_social?>.</p>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
    }
});
</script>

<?php $n_compras = count($detalle); ?>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos básicos</a></li>
        <li role="presentation"><a href="#resumen" aria-controls="resumen" role="tab" data-toggle="tab">Resumen</a></li>
<?php if ($n_compras) : ?>
        <li role="presentation"><a href="#detalle" aria-controls="detalle" role="tab" data-toggle="tab">Detalle</a></li>
        <li role="presentation"><a href="#estadisticas" aria-controls="estadisticas" role="tab" data-toggle="tab">Estadísticas</a></li>
<?php endif; ?>
<?php if ($Libro->track_id>0) : ?>
        <li role="presentation"><a href="#revision" aria-controls="revision" role="tab" data-toggle="tab">Subir revisión</a></li>
<?php endif; ?>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
    <div class="row">
        <div class="col-md-9">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Período', 'Recibidos', 'Envíados'],
    [$Libro->periodo, num($n_compras), num($Libro->documentos)],
]);
?>
        <div class="row">
            <div class="col-md-4">
                <a class="btn btn-default btn-lg btn-block<?=!$n_compras?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/csv/<?=$Libro->periodo?>" role="button">
                    <span class="far fa-file-excel" style="font-size:24px"></span>
                    Descargar CSV
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-default btn-lg btn-block<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/pdf/<?=$Libro->periodo?>" role="button">
                    <span class="far fa-file-pdf" style="font-size:24px"></span>
                    Descargar PDF
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-default btn-lg btn-block<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/xml/<?=$Libro->periodo?>" role="button">
                    <span class="far fa-file-code" style="font-size:24px"></span>
                    Descargar XML
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 center bg-info">

        <span class="lead">Track ID SII: <?=$Libro->track_id?></span>
        <p><strong><?=$Libro->revision_estado?></strong></p>
        <p><?=str_replace("\n", '<br/>', $Libro->revision_detalle)?></p>
<?php if ($Libro->track_id and $Libro->getEstado()!='LRH') : ?>
        <p>
<?php if ($Libro->track_id!=-1) : ?>
            <a class="btn btn-info" href="<?=$_base?>/dte/dte_compras/actualizar_estado/<?=$Libro->periodo?>" role="button">Actualizar estado</a><br/>
            <span style="font-size:0.8em">
                <a href="<?=$_base?>/dte/dte_compras/solicitar_revision/<?=$Libro->periodo?>" title="Solicitar nueva revisión del libro al SII">solicitar nueva revisión</a><br/>
                <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/estado_envio/<?=$Libro->track_id?>', 750, 550)" title="Ver el estado del envío en la web del SII">ver estado envío en SII</a><br/>
                <a href="<?=$_base?>/dte/dte_compras/enviar_rectificacion/<?=$Libro->periodo?>" title="Enviar rectificación del libro al SII">enviar rectificación</a>
            </span>
<?php else : ?>
            <span style="font-size:0.8em">
                <a href="<?=$_base?>/dte/dte_compras/enviar_sii/<?=$Libro->periodo?>">Generar nuevo libro</a>
            </span>
<?php endif; ?>
        </p>
<?php else: ?>
<?php if ($Libro->periodo<201708) : ?>
        <p><a class="btn btn-info" href="<?=$_base?>/dte/dte_compras/enviar_sii/<?=$Libro->periodo?>" role="button" onclick="return Form.checkSend('¿Confirmar el envio del libro al SII?')">Enviar libro al SII</a></p>
<?php else : ?>
        <p><a class="btn btn-info" href="<?=$_base?>/dte/dte_compras/enviar_sii/<?=$Libro->periodo?>" role="button" onclick="return Form.checkSend('¿Confirmar el envio del libro al SII?')">Generar libro local</a></p>
<?php endif; ?>
<?php endif; ?>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<?php if ($n_compras) : ?>

<!-- INICIO RESUMEN -->
<div role="tabpanel" class="tab-pane" id="resumen">
    <div class="panel panel-default">
        <div class="panel-heading">
            Documentos con detalle registrado
        </div>
        <div class="panel-body">
<?php
$titulos = [];
$total = ['TpoDoc' => '<strong>Total</strong>'];
foreach ($resumen as &$r) {
    $titulos = array_keys($r);
    foreach (['FctProp'] as $c) {
        unset($r[$c], $titulos[array_search($c, $titulos)]);
    }
    // sumar campos que se suman directamente
    foreach (['TotDoc', 'TotAnulado', 'TotOpExe'] as $c) {
        if (!isset($total[$c]))
            $total[$c] = 0;
        $total[$c] += $r[$c];
    }
    // sumar o restar campos segun operación
    foreach (['TotMntExe', 'TotMntNeto', 'TotMntIVA', 'TotIVAPropio', 'TotIVATerceros', 'TotLey18211', 'TotMntActivoFijo', 'TotMntIVAActivoFijo', 'TotIVANoRec', 'TotIVAUsoComun', 'TotCredIVAUsoComun', 'TotIVAFueraPlazo', 'TotOtrosImp', 'TotIVARetTotal', 'TotIVARetParcial', 'TotImpSinCredito', 'TotMntTotal', 'TotIVANoRetenido', 'TotMntNoFact', 'TotMntPeriodo'] as $c) {
        if (!isset($total[$c]))
            $total[$c] = 0;
        if (is_array($r[$c])) {
            $valor = 0;
            if ($c=='TotOtrosImp') {
                foreach ($r[$c] as $monto) {
                    $valor += $monto['TotMntImp'];
                }
            } else if ($c=='TotIVANoRec') {
                foreach ($r[$c] as $monto) {
                    $valor += $monto['TotMntIVANoRec'];
                }
            }
            $r[$c] = $valor;
        }
        if ($operaciones[$r['TpoDoc']]=='S')
            $total[$c] += $r[$c];
        else if ($operaciones[$r['TpoDoc']]=='R')
            $total[$c] -= $r[$c];
    }
    // dar formato de número
    foreach ($r as &$v) {
        if ($v)
            $v = num($v);
    }
}
foreach ($total as &$tot) {
    if (is_numeric($tot))
        $tot = $tot>0 ? num($tot) : null;
}
array_unshift($resumen, $titulos);
$resumen[] = $total;
$t = new \sowerphp\general\View_Helper_Table();
$t->setShowEmptyCols(false);
echo $t->generate($resumen);
?>
        </div>
    </div>
</div>
<!-- FIN RESUMEN -->

<!-- INICIO DETALLES -->
<div role="tabpanel" class="tab-pane" id="detalle">
<?php
array_unshift($detalle, $libro_cols);
new \sowerphp\general\View_Helper_Table($detalle);
?>
</div>
<!-- FIN DETALLES -->

<!-- INICIO ESTADÍSTICAS -->
<div role="tabpanel" class="tab-pane" id="estadisticas">
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="far fa-chart-bar fa-fw"></i> Documentos por día recibidos con fecha en el período <?=$Libro->periodo?>
    </div>
    <div class="panel-body">
        <div id="grafico-documentos_por_dia"></div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="far fa-chart-bar fa-fw"></i> Documentos por tipo recibidos con fecha en el período <?=$Libro->periodo?>
    </div>
    <div class="panel-body">
        <div id="grafico-documentos_por_tipo"></div>
    </div>
</div>
<script>
var documentos_por_dia = Morris.Line({
    element: 'grafico-documentos_por_dia',
    data: <?=json_encode($Libro->getDocumentosPorDia())?>,
    xkey: 'dia',
    ykeys: ['documentos'],
    labels: ['Documentos'],
    resize: true,
    parseTime: false
});
var documentos_por_tipo = Morris.Bar({
    element: 'grafico-documentos_por_tipo',
    data: <?=json_encode($Libro->getDocumentosPorTipo())?>,
    xkey: 'tipo',
    ykeys: ['documentos'],
    labels: ['Documentos'],
    resize: true
});
$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
    var target = $(e.target).attr("href");
    if (target=='#estadisticas') {
        documentos_por_dia.redraw();
        documentos_por_tipo.redraw();
        $(window).trigger('resize');
    }
});
</script>
</div>
<!-- FIN ESTADÍSTICAS -->

<?php endif; ?>

<?php if ($Libro->track_id>0) : ?>
<!-- INICIO REVISIÓN -->
<div role="tabpanel" class="tab-pane" id="revision">
<p>Aquí puede subir el XML con el resultado de la revisión del libro de compras envíado al SII.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_compras/subir_revision/'.$Libro->periodo, 'onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'XML revisión',
    'check' => 'notempty',
    'attr' => 'accept=".xml"',
]);
echo $f->end('Subir XML de revisión');
?>
</div>
<!-- FIN REVISIÓN -->
<?php endif; ?>

    </div>
</div>
