<ul class="nav nav-pills pull-right">
<?php if (\sowerphp\core\Configure::read('proveedores.api.libredte')) : ?>
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fa fa-bank"></span> Recibidos en SII <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a href="<?=$_base?>/dte/dte_recibidos/sii" title="Buscar los documentos recibidos en el SII">
                     DTE recibidos en SII
                </a>
            </li>
            <li>
                <a href="<?=$_base?>/dte/dte_recibidos/bhe" title="Buscar las boletas de honorarios electrónicas recibidas en el SII">
                    Boletas honorarios en SII
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>
    <li>
        <a href="<?=$_base?>/dte/dte_compras/importar" title="Importar libro IEC desde archivo CSV">
            <span class="fa fa-upload"></span> Importar CSV
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_recibidos/agregar">
            <span class="fa fa-plus"></span> Agregar documento
        </a>
    </li>
</ul>

<h1>Documentos recibidos</h1>
<p>Aquí podrá consultar todos los documentos recibidos por la empresa <?=$Emisor->razon_social?>.</p>

<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$d['intercambio'].'" title="Ver detalles del intercambio" class="btn btn-default'.(!$d['intercambio']?' disabled':'').'" role="button"><span class="fa fa-search"></span></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_recibidos/pdf/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Descargar PDF del documento" class="btn btn-default'.(!$d['intercambio']?' disabled':'').'" role="button"><span class="fa fa-file-pdf-o"></span></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_recibidos/modificar/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Modificar documento" class="btn btn-default"><span class="fa fa-edit"></span></a>';
    $d[] = $acciones;
    $d['total'] = num($d['total']);
    unset($d['emisor'], $d['dte'], $d['intercambio']);
}
$f = new \sowerphp\general\View_Helper_Form(false);
array_unshift($documentos, [
    $f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos'] + $tipos_dte, 'value'=>(isset($search['dte'])?$search['dte']:'')]),
    $f->input(['name'=>'folio', 'value'=>(isset($search['folio'])?$search['folio']:''), 'check'=>'integer']),
    $f->input(['name'=>'emisor', 'value'=>(isset($search['emisor'])?$search['emisor']:''), 'check'=>'integer', 'placeholder'=>'RUT sin dv']),
    $f->input(['type'=>'date', 'name'=>'fecha', 'value'=>(isset($search['fecha'])?$search['fecha']:''), 'check'=>'date']),
    $f->input(['name'=>'total', 'value'=>(isset($search['total'])?$search['total']:''), 'check'=>'integer']),
    $f->input(['type'=>'select', 'name'=>'usuario', 'options'=>[''=>'Todos'] + $usuarios, 'value'=>(isset($search['usuario'])?$search['usuario']:'')]),
    '<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>',
]);
array_unshift($documentos, ['Documento', 'Folio', 'Emisor', 'Fecha', 'Total', 'Usuario', 'Acciones']);

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer([
    'link' => $_base.'/dte/dte_recibidos',
    'linkEnd' => $searchUrl,
]);
$maintainer->setId('dte_recibidos_'.$Emisor->rut);
$maintainer->setColsWidth([null, null, null, null, null, null, 150]);
echo $maintainer->listar ($documentos, $paginas, $pagina, false);
