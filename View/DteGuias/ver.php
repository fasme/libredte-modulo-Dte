<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_guias" title="Volver a libro de guías de despacho" class="pull-right">
            Volver a libro guías
        </a>
    </li>
</ul>

<div class="page-header"><h1>Libro de guías de despacho período <?=$Libro->periodo?></h1></div>
<p>Esta es la página del libro de guías de despacho del período <?=$Libro->periodo?> de la empresa <?=$Emisor->razon_social?>.</p>

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
<?php if ($n_detalles) : ?>
<?php if (isset($detalle)) : ?>
        <li role="presentation"><a href="#detalle" aria-controls="detalle" role="tab" data-toggle="tab">Detalle</a></li>
<?php endif; ?>
        <li role="presentation"><a href="#estadisticas" aria-controls="estadisticas" role="tab" data-toggle="tab">Estadísticas</a></li>
<?php endif; ?>
        <li role="presentation"><a href="#revision" aria-controls="revision" role="tab" data-toggle="tab">Subir revisión</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
    <div class="row">
        <div class="col-md-9">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Período', 'Guías emitidas', 'Guías envíadas'],
    [$Libro->periodo, num($n_detalles), num($Libro->documentos)],
]);
?>
        <div class="row">
            <div class="col-md-6">
                <a class="btn btn-default btn-lg btn-block<?=!$n_detalles?' disabled':''?>" href="<?=$_base?>/dte/dte_guias/csv/<?=$Libro->periodo?>" role="button">
                    <span class="far fa-file-excel" style="font-size:24px"></span>
                    Descargar detalle en archivo CSV
                </a>
            </div>
            <div class="col-md-6">
                <a class="btn btn-default btn-lg btn-block<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_guias/xml/<?=$Libro->periodo?>" role="button">
                    <span class="far fa-file-code" style="font-size:24px"></span>
                    Descargar libro de guías en XML
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 center bg-info">
        <span class="lead">Track ID SII: <?=$Libro->track_id?></span>
        <p><strong><?=$Libro->revision_estado?></strong></p>
        <p><?=str_replace("\n", '<br/>', $Libro->revision_detalle)?></p>
<?php if ($Libro->track_id) : ?>
        <p>
            <a class="btn btn-info" href="<?=$_base?>/dte/dte_guias/actualizar_estado/<?=$Libro->periodo?>" role="button">Actualizar estado</a><br/>
            <span style="font-size:0.8em">
                <a href="<?=$_base?>/dte/dte_guias/solicitar_revision/<?=$Libro->periodo?>" title="Solicitar nueva revisión del libro al SII">solicitar nueva revisión</a><br/>
                <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/estado_envio/<?=$Libro->track_id?>', 750, 550)" title="Ver el estado del envío en la web del SII">ver estado envío en SII</a><br/>
                <a href="<?=$_base?>/dte/dte_guias/enviar_sii/<?=$Libro->periodo?>" title="Volver a enviar el libro de guías al SII" onclick="return Form.checkSend('¿Confirmar reenvío del libro al SII?')">reenviar libro al SII</a>
            </span>
        </p>
<?php else: ?>
        <p><a class="btn btn-info" href="<?=$_base?>/dte/dte_guias/enviar_sii/<?=$Libro->periodo?>" role="button">Enviar libro al SII</a></p>
<?php endif; ?>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<?php if ($n_detalles) : ?>

<?php if (isset($detalle)) : ?>
<!-- INICIO DETALLES -->
<div role="tabpanel" class="tab-pane" id="detalle">
<?php
array_unshift($detalle, $libro_cols);
new \sowerphp\general\View_Helper_Table($detalle);
?>
</div>
<!-- FIN DETALLES -->
<?php endif; ?>

<!-- INICIO ESTADÍSTICAS -->
<div role="tabpanel" class="tab-pane" id="estadisticas">
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="far fa-chart-bar fa-fw"></i> Guías por día emitidas con fecha en el período <?=$Libro->periodo?>
    </div>
    <div class="panel-body">
        <div id="grafico-documentos_por_dia"></div>
    </div>
</div>
</div>
<!-- FIN ESTADÍSTICAS -->

<?php endif; ?>

<!-- INICIO REVISIÓN -->
<div role="tabpanel" class="tab-pane" id="revision">
<p>Aquí puede subir el XML con el resultado de la revisión del libro de guías de despacho envíado al SII.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_guias/subir_revision/'.$Libro->periodo, 'onsubmit'=>'Form.check()']);
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
$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
    var target = $(e.target).attr("href");
    if (target=='#estadisticas') {
        documentos_por_dia.redraw();
        $(window).trigger('resize');
    }
});
</script>
