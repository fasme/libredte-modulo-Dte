<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/registro_compras" title="Explorar el registro de compras del SII" class="nav-link">
            <i class="fas fa-university"></i>
            Registro compras SII
        </a>
    </li>
</ul>
<div class="page-header"><h1>Documentos recibidos pendientes en SII</h1></div>
<p>Los siguientes documentos han sido recibidos en el SII y actualmente se encuentran pendientes, no han sido procesados.</p>
<p>Los documentos son automáticamente registrados como recibidos por el receptor después de 8 días desde que el SII los recibe. Por lo cual si aquí existe algún documento que no corresponda debe reclamar el mismo o marcar como no incluir en el SII.</p>
<?php
foreach ($pendientes as &$p) {
    $p['fecha'] = \sowerphp\general\Utility_Date::format($p['fecha']);
    $p['fecha_recepcion_sii'] = \sowerphp\general\Utility_Date::format($p['fecha_recepcion_sii'], 'd/m/Y H:i');
    $p['proveedor_razon_social'] .= '<span>'.num($p['proveedor_rut']).'-'.$p['proveedor_dv'].'</span>';
    $p['dte_glosa'] .= ' N° '.$p['folio'];
    foreach (['exento', 'neto', 'iva', 'total'] as $col) {
        if ($p[$col]) {
            $p[$col] = num($p[$col]);
        }
    }
    unset($p['estado'], $p['proveedor_rut'], $p['proveedor_dv'], $p['dte'], $p['folio'], $p['dettipotransaccion']);
}
array_unshift($pendientes, ['Proveedor','Documento', 'Fecha', 'Recepción SII', 'Exento', 'Neto', 'IVA', 'Total', 'Tipo']);
new \sowerphp\general\View_Helper_Table($pendientes);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/registro_compras/pendientes_csv?<?=http_build_query($filtros)?>" role="button">Descargar detalle de documentos pendientes</a>
