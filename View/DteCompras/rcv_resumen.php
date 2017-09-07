<ul class="nav nav-pills pull-right">
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fa fa-bank"></span> Ver resumen RC<span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>">
                     Registrados
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/PENDIENTE">
                    Pendientes
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/NO_INCLUIR">
                    No incluídos
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/RECLAMADO">
                    Reclamados
                </a>
            </li>
        </ul>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_compras/ver/<?=$periodo?>" title="Volver al IEC de <?=$periodo?>">
            Volver al período
        </a>
    </li>
</ul>
<h1>Resumen RC período <?=$periodo?> <small>estado: <?=$estado?></small></h1>
<p>Esta es la página del resumen del registro de compras del período <?=$periodo?> de la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($resumen as &$r) {
    foreach(['rsmnMntExe', 'rsmnMntNeto', 'rsmnMntIVA', 'rsmnMntIVANoRec', 'rsmnIVAUsoComun', 'rsmnMntTotal', 'rsmnTotDoc'] as $col) {
        $r[$col] = num($r[$col]);
    }
    $r[] = $r['rsmnLink'] ? ('<a href="'.$_base.'/dte/dte_compras/rcv_detalle/'.$periodo.'/'.$r['rsmnTipoDocInteger'].'/'.$estado.'" title="Ver detalles de los documentos"><span class="fa fa-search btn btn-default"></a>') : '';
    unset($r['dcvCodigo'], $r['rsmnCodigo'], $r['rsmnTipoDocInteger'], $r['rsmnLink'], $r['rsmnEstadoContab'], $r['rsmnTotalRutEmisor']);
}
array_unshift($resumen, ['DTE', 'Ingreso', 'Exento', 'Neto', 'IVA', 'IVA no rec.', 'IVA uso común', 'Total', 'Docs', 'Ver']);
new \sowerphp\general\View_Helper_Table($resumen, 'rc_resumen_'.$periodo.'_'.$estado, true);
