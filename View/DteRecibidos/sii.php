<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/registro_compras" title="Ir al registro de compra del SII" class="nav-link">
            <i class="fas fa-university"></i>
            Registro de compras
        </a>
    </li>
    <li class="nab-item">
        <a href="<?=$_base?>/dte/dte_recibidos/listar" class="nav-link">
            <i class="fa fa-sign-in-alt"></i>
            Documentos recibidos
        </a>
    </li>
</ul>
<div class="page-header"><h1>DTE recibidos en SII <small>previo a RC</small></h1></div>
<p>Aquí podrá consultar los documentos que el SII recibió para la empresa <?=$Emisor->razon_social?>.</p>
<div class="row">
    <div class="col-md-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => !empty($_POST['desde']) ? $_POST['desde'] : $hoy,
    'check' => 'notempty date',
    'help' => 'Desde qué fecha de recepción en el SII buscar',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => !empty($_POST['hasta']) ? $_POST['hasta'] : $hoy,
    'check' => 'notempty date',
    'help' => 'Hasta qué fecha de recepción en el SII buscar',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'excluir_en_libro',
    'label' => '¿Buscar todo?',
    'options' => ['Buscar todos los documentos recibidos en el SII', 'Buscar sólo los documentos que no están en el libro de compras'],
    'help' => '¿Excluir de la búsqueda lo que ya está ingresado al libro de compras?',
]);
echo $f->end('Buscar documentos');
?>
    </div>
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-header"><i class="fas fa-exclamation-circle text-warning"></i> Nuevo registro de compras</div>
            <div class="card-body">
                Con la entrada en vigencia en agosto de 2017 del registro de compras esta opción queda obsoleta y se debe usar la <a href="<?=$_base?>/dte/dte_compras/registro_compras">búsqueda directa en el registro</a> desde esa fecha en adelante.
            </div>
        </div>
    </div>
</div>

<?php
if (isset($documentos)) {
    $DteTipos = new \website\Dte\Admin\Mantenedores\Model_DteTipos();
    foreach ($documentos as &$d) {
        $acciones = '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/verificar_datos/'.$Emisor->getRUT().'/'.$d['dte'].'/'.$d['folio'].'/'.$d['emision'].'/'.$d['total'].'/'.$d['rut'].'\', 750, 550)" title="Verificar datos del documento en la web del SII" class="btn btn-default"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="#" onclick="__.popup(\''.$_base.'/dte/sii/dte_rcv/'.$d['rut'].'/'.$d['dte'].'/'.$d['folio'].'\', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII" class="btn btn-default"><i class="fa fa-eye fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/dte_rcv/'.$d['rut'].'/'.$d['dte'].'/'.$d['folio'].'" title="Ingresar acción del registro de compra/venta en el SII" class="btn btn-default"><i class="fa fa-check fa-fw"></i></a>';
        $d[] = \sowerphp\general\Utility_Date::count(\sowerphp\general\Utility_Date::format($d['recepcion'], 'Y-m-d'));
        $d[] = $acciones;
        if (is_numeric($d['dte'])) {
            $d['documento'] = $DteTipos->get($d['dte'])->tipo;
        }
        $d['emision'] = \sowerphp\general\Utility_Date::format($d['emision']);
        $d['recepcion'] = \sowerphp\general\Utility_Date::format($d['recepcion'], 'd/m/Y H:i');
        $d['total'] = num($d['total']);
        unset($d['dte']);
    }
    array_unshift($documentos, ['RUT', 'Razón social', 'Documento', 'Folio', 'Fecha emisión', 'Total', 'Fecha recepción', 'Track ID', 'Días', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setID('dte_recibidos_sii_'.$Emisor->rut.'_'.$_POST['desde'].'_'.$_POST['hasta']);
    $t->setExport(true);
    $t->setColsWidth([null, null, null, null, null, null, null, null, null, 120]);
    echo $t->generate($documentos);
?>
<link rel="stylesheet" type="text/css" href="<?=$_base?>/css/jquery.dataTables.css" />
<script type="text/javascript" src="<?=$_base?>/js/jquery.dataTables.js"></script>
<script type="text/javascript"> $(document).ready(function(){ dataTable("#dte_recibidos_sii_<?=$Emisor->rut?>_<?=$_POST['desde']?>_<?=$_POST['hasta']?>"); }); </script>
<?php
}
