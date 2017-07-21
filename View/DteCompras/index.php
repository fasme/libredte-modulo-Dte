<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_compras/importar" title="Importar libro IEC desde archivo CSV">
            <span class="fa fa-upload"></span> Importar CSV
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_ventas" title="Ir al libro de ventas">
            <span class="fa fa-book"></span> Libro de ventas
        </a>
    </li>
</ul>
<h1>Libro de compras (IEC)</h1>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="dte_compras/ver/'.$p['periodo'].'" title="Ver estado del libro del período"><span class="fa fa-search btn btn-default"></span></a>';
    if ($p['recibidos'])
        $acciones .= ' <a href="dte_compras/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período"><span class="fa fa-file-excel-o btn btn-default"></span></a>';
    else
        $acciones .= ' <span class="fa fa-file-excel-o btn btn-default disabled"></span>';
    $p[] = $acciones;
}
array_unshift($periodos, ['Período','Recibidos', 'Envíados', 'Track ID', 'Estado', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_compras/sin_movimientos" role="button">Enviar libro de compras sin movimientos</a>
