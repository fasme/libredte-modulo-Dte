<ul class="nav nav-pills pull-right">
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
        <li role="presentation"><a href="#revision" aria-controls="revision" role="tab" data-toggle="tab">Subir revisión</a></li>
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
                    <span class="fa fa-file-excel-o" style="font-size:24px"></span>
                    Descargar CSV
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-default btn-lg btn-block<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/pdf/<?=$Libro->periodo?>" role="button">
                    <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
                    Descargar PDF
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-default btn-lg btn-block<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/xml/<?=$Libro->periodo?>" role="button">
                    <span class="fa fa-file-code-o" style="font-size:24px"></span>
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
            <a class="btn btn-info" href="<?=$_base?>/dte/dte_compras/actualizar_estado/<?=$Libro->periodo?>" role="button">Actualizar estado</a><br/>
            <span style="font-size:0.8em">
                <a href="<?=$_base?>/dte/dte_compras/solicitar_revision/<?=$Libro->periodo?>" title="Solicitar nueva revisión del libro al SII">solicitar nueva revisión</a><br/>
                <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/estado_envio/<?=$Libro->track_id?>', 750, 550)" title="Ver el estado del envío en la web del SII">ver estado envío en SII</a><br/>
                <a href="<?=$_base?>/dte/dte_compras/enviar_rectificacion/<?=$Libro->periodo?>" title="Enviar rectificación del libro al SII">enviar rectificación</a>
            </span>
        </p>
<?php else: ?>
        <p><a class="btn btn-info" href="<?=$_base?>/dte/dte_compras/enviar_sii/<?=$Libro->periodo?>" role="button" onclick="return Form.checkSend('¿Confirmar el envio del libro al SII?')">Enviar libro al SII</a></p>
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
    <img src="<?=$_base.'/dte/dte_compras/grafico_documentos_diarios/'.$Libro->periodo?>" alt="Gráfico compras diarias del período" class="img-responsive thumbnail center" />
    <br/>
    <img src="<?=$_base.'/dte/dte_compras/grafico_tipos/'.$Libro->periodo?>" alt="Gráfico con tipos de compras del período" class="img-responsive thumbnail center" />
</div>
<!-- FIN ESTADÍSTICAS -->

<?php endif; ?>

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

    </div>
</div>
