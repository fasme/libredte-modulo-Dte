<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/ver/<?=$periodo?>" title="Ir al libro de compras (IEC) del período <?=$periodo?>" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de compras <?=$periodo?>
        </a>
    </li>
</ul>
<div class="page-header"><h1>Sincronización tipo de transacciones del período <?=$periodo?></h1></div>
<?php
foreach ($datos as &$d) {
    $d[] = '<a href="'.$_base.'/dte/dte_recibidos/modificar/'.substr($d[0],0,-2).'/'.$d[1].'/'.$d[2].'" class="btn btn-primary"><i class="fa fa-edit fa-fw"></i></a>';
}
array_unshift($datos, ['RUT emisor', 'Tipo DTE', 'Folio', 'Tipo transacción', 'Código IVA e impuestos.']);
new \sowerphp\general\View_Helper_Table($datos, 'tipo_transacciones_'.$periodo, true);
