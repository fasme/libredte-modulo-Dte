<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/solicitar_caf/<?=$DteFolio->dte?>" title="Solicitar timbraje electrónico al SII" class="nav-link">
            <i class="fa fa-download"></i> Solicitar CAF
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/modificar/<?=$DteFolio->dte?>" title="Modificar el mantenedor de folios" class="nav-link">
            <i class="fas fa-edit"></i> Modificar mantenedor
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>

<div class="page-header"><h1>Folios de <?=$DteFolio->getTipo()->tipo?></h1></div>

<div class="card-deck">
    <div class="card mb-4">
        <div class="card-body text-center">
            <p class="small">siguiente folio disponible</p>
            <p class="text-info lead"><?=num($DteFolio->siguiente)?></p>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-center">
            <p class="small">total folios disponibles</p>
            <p class="text-info lead"><?=num($DteFolio->disponibles)?></p>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-center">
            <p class="small"><?=$Emisor->config_sii_timbraje_automatico?'timbrar':'alertar'?> si se llega a esta cantidad</p>
            <p class="text-info lead"><?=num($DteFolio->alerta)?></p>
        </div>
    </div>
</div>

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
        <li class="nav-item"><a href="#caf" aria-controls="caf" role="tab" data-toggle="tab" id="caf-tab" class="nav-link active" aria-selected="true">Archivos CAF</a></li>
        <li class="nav-item"><a href="#uso_mensual" aria-controls="caf" role="tab" data-toggle="tab" id="uso_mensual-tab" class="nav-link">Folios usados mensualmente</a></li>
        <li class="nav-item"><a href="#sin_uso" aria-controls="caf" role="tab" data-toggle="tab" id="sin_uso-tab" class="nav-link">Folios sin uso</a></li>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO ARCHIVOS CAF -->
<div role="tabpanel" class="tab-pane active" id="caf" aria-labelledby="caf-tab">
<?php
$hoy = date('Y-m-d');
$cafs = $DteFolio->getCafs('DESC');
foreach ($cafs as &$caf) {
    $caf['fecha_autorizacion'] = \sowerphp\general\Utility_Date::format($caf['fecha_autorizacion']);
    $caf['en_uso'] = ($DteFolio->siguiente >= $caf['desde'] and $DteFolio->siguiente <= $caf['hasta']) ? '<i class="fa fa-check"></i>' : '';
    $acciones = '';
    if (!in_array($DteFolio->dte, [39, 41])) {
        $acciones .= '<a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/recibidos" title="Descargar folios recibidos en SII del CAF que inicia en '.$caf['desde'].'" class="btn btn-primary"><i class="far fa-check-circle fa-fw"></i></a> ';
        $acciones .= '<a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/anulados" title="Descargar folios anulados en SII del CAF que inicia en '.$caf['desde'].'" class="btn btn-primary"><i class="fas fa-ban fa-fw"></i></a> ';
        $acciones .= '<a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/pendientes" title="Descargar folios pendientes en SII del CAF que inicia en '.$caf['desde'].'" class="btn btn-primary"><i class="fab fa-creative-commons-share fa-fw"></i></a> ';
    }
    $acciones .= '<a href="../xml/'.$DteFolio->dte.'/'.$caf['desde'].'" title="Descargar archivo XML del CAF que inicia en '.$caf['desde'].'" class="btn btn-primary"><i class="fas fa-code fa-fw"></i></a>';
    $caf[] = $acciones;
}
array_unshift($cafs, ['Desde', 'Hasta', 'Cantidad', 'Fecha autorización', 'Meses autorización', 'En uso', 'Descargar']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, !in_array($DteFolio->dte, [39, 41]) ? 200 : 100]);
echo $t->generate($cafs);
?>
</div>
<!-- FIN ARCHIVOS CAF -->

<!-- INICIO ESTADISTICA -->
<div role="tabpanel" class="tab-pane" id="uso_mensual" aria-labelledby="uso_mensual-tab">
<?php
$foliosMensuales = $DteFolio->getUsoMensual(24, 'DESC');
array_unshift($foliosMensuales, ['Período', 'Cantidad usada']);
new \sowerphp\general\View_Helper_Table($foliosMensuales, 'uso_mensual_folios_'.$DteFolio->emisor.'_'.$DteFolio->dte, true);
?>
</div>
<!-- FIN ESTADISTICA -->

<!-- INICIO ESTADISTICA -->
<div role="tabpanel" class="tab-pane" id="sin_uso" aria-labelledby="sin_uso-tab">
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
