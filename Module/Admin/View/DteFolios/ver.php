<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/admin/dte_folios/solicitar_caf/<?=$DteFolio->dte?>" title="Solicitar timbraje electrónico al SII">
            <span class="fa fa-download"></span> Solicitar timbraje al SII
        </a>
    </li>
</ul>

<div class="page-header"><h1>Mantenedor folios <?=$DteFolio->getTipo()->tipo?> <small>código <?=$DteFolio->dte?></small></h1></div>

<div class="row">
    <div class="col-sm-4 text-center well">
        <span class="text-info lead"><?=num($DteFolio->siguiente)?></span><br/>
        <small>siguiente folio disponible</small>
    </div>
    <div class="col-sm-4 text-center well">
        <span class="text-info lead"><?=num($DteFolio->disponibles)?></span><br/>
        <small>folios disponibles</small>
    </div>
    <div class="col-sm-4 text-center well">
        <span class="text-info lead"><?=num($DteFolio->alerta)?></span><br/>
        <small>alertar si se llega a este número</small>
    </div>
</div>

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
        <li role="presentation" class="active"><a href="#caf" aria-controls="caf" role="tab" data-toggle="tab">Archivos CAF</a></li>
        <li role="presentation"><a href="#uso_mensual" aria-controls="caf" role="tab" data-toggle="tab">Folios usados mensualmente</a></li>
        <li role="presentation"><a href="#sin_uso" aria-controls="caf" role="tab" data-toggle="tab">Folios sin uso</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO ARCHIVOS CAF -->
<div role="tabpanel" class="tab-pane active" id="caf">
<?php
$hoy = date('Y-m-d');
$cafs = $DteFolio->getCafs();
foreach ($cafs as &$caf) {
    $caf['fecha_autorizacion'] = \sowerphp\general\Utility_Date::format($caf['fecha_autorizacion']);
    $caf['en_uso'] = ($DteFolio->siguiente >= $caf['desde'] and $DteFolio->siguiente <= $caf['hasta']) ? 'X' : '';
    $acciones = '<a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/recibidos" title="Descargar folios recibidos en SII del CAF que inicia en '.$caf['desde'].'"><span class="far fa-check-circle btn btn-default"></span></a>';
    $acciones .= ' <a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/anulados" title="Descargar folios anulados en SII del CAF que inicia en '.$caf['desde'].'"><span class="fas fa-ban btn btn-default"></span></a>';
    $acciones .= ' <a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/pendientes" title="Descargar folios pendientes en SII del CAF que inicia en '.$caf['desde'].'"><span class="fab fa-creative-commons-share btn btn-default"></span></a>';
    $acciones .= ' <a href="../xml/'.$DteFolio->dte.'/'.$caf['desde'].'" title="Descargar CAF que inicia en '.$caf['desde'].'"><span class="fas fa-code btn btn-default"></span></a>';
    $caf[] = $acciones;
}
array_unshift($cafs, ['Desde', 'Hasta', 'Cantidad', 'Fecha autorización', 'Meses autorización', 'En uso', 'Descargar']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, 200]);
echo $t->generate($cafs);
?>
</div>
<!-- FIN ARCHIVOS CAF -->

<!-- INICIO ESTADISTICA -->
<div role="tabpanel" class="tab-pane" id="uso_mensual">
<?php
$foliosMensuales = $DteFolio->getUsoMensual(24);
array_unshift($foliosMensuales, ['Período', 'Cantidad usada']);
new \sowerphp\general\View_Helper_Table($foliosMensuales, 'uso_mensual_folios_'.$DteFolio->emisor.'_'.$DteFolio->dte, true);
?>
</div>
<!-- FIN ESTADISTICA -->

<!-- INICIO ESTADISTICA -->
<div role="tabpanel" class="tab-pane" id="sin_uso">
<?php
$foliosSinUso = $DteFolio->getSinUso();
if ($foliosSinUso) :
    foreach ($foliosSinUso as &$folioSinUso) {
        $folioSinUso = '<a href="#" onclick="__.popup(\''.$_base.'/dte/admin/dte_folios/estado/'.$DteFolio->dte.'/'.$folioSinUso.'\', 750, 550); return false" title="Ver el estado del folio '.$folioSinUso.' en el SII">'.$folioSinUso.'</a>';
    }
?>
<p>Los folios a continuación, que están entre el N° <?=$DteFolio->getPrimerFolio()?> (primer folio emitido en LibreDTE) y el N° <?=$DteFolio->siguiente?> (folio siguiente), se encuentran sin uso en el sistema:</p>
<p><?=implode(', ', $foliosSinUso)?></p>
<p>Si estos folios no existen en otro sistema de facturación y no los recuperará, debe <a href="<?=\sasco\LibreDTE\Sii::getURL('/anulacionMsvDteInternet', $Emisor->config_ambiente_en_certificacion)?>" target="_blank">anularlos en el SII</a>.
<?php else : ?>
<p>No hay CAF con folios sin uso menores al folio siguiente <?=$DteFolio->siguiente?>.</p>
<?php endif; ?>
</div>
<!-- FIN ESTADISTICA -->

    </div>
</div>

<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base?>/dte/admin/dte_folios">Volver al mantenedor de folios</a>
</div>
