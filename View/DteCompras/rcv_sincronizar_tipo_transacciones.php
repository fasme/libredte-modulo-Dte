<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_compras/ver/<?=$periodo?>" title="Volver a la IEC del período <?=$periodo?>">
            Volver al período
        </a>
    </li>
</ul>
<h1>Sincronización tipo de transacciones del período <?=$periodo?></h1>
<?php
foreach ($datos as &$d) {
    $d[] = '<a href="'.$_base.'/dte/dte_recibidos/modificar/'.substr($d[0],0,-2).'/'.$d[1].'/'.$d[2].'" class="btn btn-default"><span class="fa fa-edit"></span></a>';
}
array_unshift($datos, ['Rut-DV', 'Codigo_Tipo_Doc', 'Folio_Doc', 'TpoTranCompra', 'Codigo_IVA_E_Imptos']);
new \sowerphp\general\View_Helper_Table($datos, 'tipo_transacciones_'.$periodo, true);
