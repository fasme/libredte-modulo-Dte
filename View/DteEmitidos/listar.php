<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/documentos/emitir" title="Emitir documento temporal">
            <span class="fa fa-edit"></span> Emitir documento
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_emitidos/cargar_xml" title="Cargar un XML emitido externamente">
            <span class="fa fa-upload"></span> Cargar XML
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_emitidos/buscar" title="Búsqueda avanzada de documentos emitidos">
            <span class="fa fa-search"></span> Buscar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Documentos emitidos</h1></div>
<p>Aquí podrá consultar todos los documentos emitidos por la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento"><span class="far fa-file-pdf btn btn-default"></span></a>';
    $d[] = $acciones;
    $d['total'] = num($d['total']);
    $d['sucursal_sii'] = $Emisor->getSucursal($d['sucursal_sii'])->sucursal;
    unset($d['dte']);
}
$f = new \sowerphp\general\View_Helper_Form(false);
array_unshift($documentos, [
    $f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos'] + $tipos_dte, 'value'=>(isset($search['dte'])?$search['dte']:'')]),
    $f->input(['name'=>'folio', 'value'=>(isset($search['folio'])?$search['folio']:''), 'check'=>'integer']),
    $f->input(['name'=>'receptor', 'value'=>(isset($search['receptor'])?$search['receptor']:''), 'check'=>'integer', 'placeholder'=>'RUT sin dv']),
    $f->input(['type'=>'date', 'name'=>'fecha', 'value'=>(isset($search['fecha'])?$search['fecha']:''), 'check'=>'date']),
    $f->input(['name'=>'total', 'value'=>(isset($search['total'])?$search['total']:''), 'check'=>'integer']),
    '',
    '',
    $f->input(['type'=>'select', 'name'=>'sucursal_sii', 'options'=>[''=>'Todas'] + $sucursales, 'value'=>(isset($search['sucursal_sii'])?$search['sucursal_sii']:$sucursal)]),
    $f->input(['type'=>'select', 'name'=>'usuario', 'options'=>[''=>'Todos'] + $usuarios, 'value'=>(isset($search['usuario'])?$search['usuario']:'')]),
    '<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>',
]);
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Intercambio', 'Sucursal', 'Usuario', 'Acciones']);

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer([
    'link' => $_base.'/dte/dte_emitidos',
    'linkEnd' => $searchUrl,
]);
$maintainer->setId('dte_emitidos_'.$Emisor->rut);
$maintainer->setColsWidth([null, null, null, null, null, null, null, null, null, 100]);
echo $maintainer->listar ($documentos, $paginas, $pagina, false);
