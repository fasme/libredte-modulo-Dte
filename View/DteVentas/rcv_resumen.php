<ul class="nav nav-pills pull-right">
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fas fa-download"></span> Descargar<span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a href="<?=$_base?>/dte/dte_ventas/rcv_csv/<?=$periodo?>/rcv">
                     En formato RV
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_ventas/rcv_csv/<?=$periodo?>/iecv">
                     En formato IEV
                </a>
            </li>
        </ul>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_ventas/ver/<?=$periodo?>" title="Volver al IEV de <?=$periodo?>">
            Volver al período
        </a>
    </li>
</ul>
<div class="page-header"><h1>Resumen RV período <?=$periodo?></h1></div>
<p>Esta es la página del resumen del registro de ventas del período <?=$periodo?> de la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($resumen as &$r) {
    foreach(['rsmnMntExe', 'rsmnMntNeto', 'rsmnMntIVA', 'rsmnMntIVANoRec', 'rsmnIVAUsoComun', 'rsmnMntTotal', 'rsmnTotDoc'] as $col) {
        $r[$col] = num($r[$col]);
    }
    $r[] = $r['rsmnLink'] ? ('<a href="'.$_base.'/dte/dte_ventas/rcv_detalle/'.$periodo.'/'.$r['rsmnTipoDocInteger'].'" title="Ver detalles de los documentos"><span class="fa fa-search btn btn-default"></a>') : '';
    unset($r['dcvCodigo'], $r['rsmnCodigo'], $r['rsmnTipoDocInteger'], $r['rsmnLink'], $r['dcvOperacion'], $r['rsmnEstadoContab'], $r['rsmnTotalRutEmisor']);
}
array_unshift($resumen, ['DTE', 'Ingreso', 'Exento', 'Neto', 'IVA', 'IVA no rec.', 'IVA uso común', 'Total', 'Docs', 'Ver']);
new \sowerphp\general\View_Helper_Table($resumen, 'rv_resumen_'.$periodo, true);
