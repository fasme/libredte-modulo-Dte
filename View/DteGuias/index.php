<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_guias/facturar" title="Facturar masivamente guías de despacho">
            Facturación masiva
        </a>
    </li>
</ul>
<h1>Libro de guías de despacho</h1>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="dte_guias/ver/'.$p['periodo'].'" title="Ver estado del libro del período"><span class="fa fa-search btn btn-default"></span></a>';
    if ($p['emitidos'])
        $acciones .= ' <a href="dte_guias/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período"><span class="fa fa-file-excel-o btn btn-default"></span></a>';
    else
        $acciones .= ' <span class="fa fa-file-excel-o btn btn-default disabled"></span>';
    $p[] = $acciones;
}
array_unshift($periodos, ['Período', 'Emitidas', 'Envíadas', 'Track ID', 'Estado', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_guias/sin_movimientos" role="button">Enviar libro de guías sin movimientos</a>
