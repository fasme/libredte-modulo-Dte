<h1>Mantenedor folios <?=$DteFolio->getTipo()->tipo?> <small>código <?=$DteFolio->dte?></small></h1>

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
    $caf[] = '<a href="../xml/'.$DteFolio->dte.'/'.$caf['desde'].'" title="Descargar CAF que inicia en '.$caf['desde'].' del DTE '.$DteFolio->dte.'"><span class="fa fa-download btn btn-default"></span></a>';
}
array_unshift($cafs, ['Desde', 'Hasta', 'Fecha autorización', 'Meses autorización', 'En uso', 'Descargar']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, 100]);
echo $t->generate($cafs);
?>
</div>
<!-- FIN ARCHIVOS CAF -->

    </div>
</div>

<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base?>/dte/admin/dte_folios">Volver al mantenedor de folios</a>
</div>
