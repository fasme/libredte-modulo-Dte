<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_ventas/registro_ventas" title="Explorar el registro de ventas del SII">
            <span class="fas fa-university"></span>
            Registro ventas SII
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_emitidos/buscar" title="Búsqueda avanzada de documentos emitidos">
            <span class="fa fa-search"></span> Buscar
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_ventas/resumen" title="Ver resumen del libro de ventas">
            <span class="fa fa-list"></span> Resumen
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_compras" title="Ir al libro de compras">
            <span class="fa fa-book"></span> Libro de compras
        </a>
    </li>
</ul>
<div class="page-header"><h1>Libro de ventas (IEV)</h1></div>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="dte_ventas/ver/'.$p['periodo'].'" title="Ver estado del libro del período"><span class="fa fa-search btn btn-default"></span></a>';
    if ($p['emitidos'])
        $acciones .= ' <a href="dte_ventas/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período"><span class="far fa-file-excel btn btn-default"></span></a>';
    else
        $acciones .= ' <span class="far fa-file-excel btn btn-default disabled"></span>';
    $p[] = $acciones;
}
array_unshift($periodos, ['Período','Emitidos', 'Envíados', 'Track ID', 'Estado', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_ventas/sin_movimientos" role="button">Enviar libro de ventas sin movimientos</a>
