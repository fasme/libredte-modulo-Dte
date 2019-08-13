<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/registro_compras" title="Explorar el registro de compras del SII" class="nav-link">
            <i class="fas fa-university"></i>
            Registro compras SII
        </a>
    </li>
    <li class="nav-item" class="dropdown">
        <a href="<?=$_base?>/dte/registro_compras/buscar" title="Buscar documentos recibidos pendientes en SII" class="nav-link">
            <i class="fas fa-search"></i>
            Buscar
        </a>
    </li>
    <li class="nav-item" class="dropdown">
        <a href="<?=$_base?>/dte/registro_compras/actualizar" title="Actualizar documentos recibidos pendientes en SII" class="nav-link">
            <i class="fas fa-sync"></i>
            Actualizar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Documentos recibidos pendientes en SII</h1></div>
<p>Los siguientes documentos han sido recibidos en el SII y actualmente se encuentran pendientes, no han sido procesados.</p>
<p>Los documentos son automáticamente registrados como recibidos por el receptor después de 8 días desde que el SII los recibe. Por lo cual si aquí existe algún documento que no corresponda debe reclamar el mismo o marcar como no incluir en el SII.</p>
<?php
foreach ($documentos as &$d) {
    $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
    $d['fecha_recepcion_sii'] = \sowerphp\general\Utility_Date::format($d['fecha_recepcion_sii'], 'd/m/Y H:i');
    $d['proveedor_razon_social'] .= '<span>'.num($d['proveedor_rut']).'-'.$d['proveedor_dv'].'</span>';
    $d['dte_glosa'] .= ' N° '.$d['folio'];
    foreach (['exento', 'neto', 'iva', 'total'] as $col) {
        if ($d[$col]) {
            $d[$col] = num($d[$col]);
        }
    }
    unset($d['estado'], $d['proveedor_rut'], $d['proveedor_dv'], $d['dte'], $d['folio'], $d['dettipotransaccion'], $d['desctipotransaccion']);
}
array_unshift($documentos, ['Proveedor','Documento', 'Fecha', 'Recepción SII', 'Exento', 'Neto', 'IVA', 'Total']);
new \sowerphp\general\View_Helper_Table($documentos);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/registro_compras/csv?<?=http_build_query($filtros)?>" role="button">Descargar detalle de documentos</a>
