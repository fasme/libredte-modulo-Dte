<ul class="nav nav-pills pull-right">
<?php if (!$Emisor->config_ambiente_en_certificacion) : ?>
    <li>
        <a href="https://www4.sii.cl/consdcvinternetui/#/home" title="Ir al registro de compra y venta en el SII" target="_blank">
            <span class="fas fa-university"></span>
            Ver RCV en SII
        </a>
    </li>
<?php endif; ?>
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
<div class="page-header"><h1>Libro de compras (IEC)</h1></div>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="dte_compras/ver/'.$p['periodo'].'" title="Ver estado del libro del período"><span class="fa fa-search btn btn-default"></span></a>';
    if ($p['recibidos'])
        $acciones .= ' <a href="dte_compras/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período"><span class="far fa-file-excel btn btn-default"></span></a>';
    else
        $acciones .= ' <span class="far fa-file-excel btn btn-default disabled"></span>';
    $p[] = $acciones;
}
array_unshift($periodos, ['Período','Recibidos', 'Envíados', 'Track ID', 'Estado', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_compras/sin_movimientos" role="button">Enviar libro de compras sin movimientos</a>
