<ul class="nav nav-pills pull-right">
<?php if ($soloPendientes) : ?>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/listar" title="Listar todos los intercambios paginados">
            <span class="fa fa-search"></span>
            Listar todo
        </a>
    </li>
<?php else : ?>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/listar/0/1" title="Ver todos los documentos pendientes de procesar">
            <span class="fa fa-search"></span>
            Ver todo lo pendiente
        </a>
    </li>
<?php endif; ?>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/pendientes" title="Descargar los documentos pendientes de procesar">
            <span class="fa fa-download"></span>
            Descargar pendientes
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/actualizar" title="Actualizar bandeja de intercambio <?=$Emisor->config_email_intercambio_user?>">
            <span class="fa fa-refresh"></span>
            Actualizar bandeja
        </a>
    </li>
</ul>

<h1>Bandeja de intercambio de DTE</h1>
<p>Aquí podrá revisar, aceptar o rechazar aquellos documentos que otros contribuyentes han envíado a <?=$Emisor->razon_social?> de manera electrónica.</p>

<?php
foreach ($intercambios as &$i) {
    if (!is_numeric($i['documentos'])) {
        $documentos = explode('|', $i['documentos']);
        foreach ($documentos as &$d) {
            $aux = explode(',', $d);
            if (isset($aux[1])) {
                list($tipo, $folio) = $aux;
                $d = 'T'.$tipo.'F'.$folio;
            }
        }
        $i['documentos'] = implode('<br/>', $documentos);
    }
    if ($soloPendientes) {
        $i[] = '';
        $i[] = '';
    }
    $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$i['codigo'].'" title="Ver detalles del intercambio"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$i['codigo'].'" title="Descargar PDF del intercambio"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
    $i[] = $acciones;
    if (is_numeric($i['emisor'])) {
        $i['emisor'] = \sowerphp\app\Utility_Rut::addDV($i['emisor']);
    }
}
array_unshift($intercambios, ['Código', 'Emisor', 'Firmado', 'Recibido', 'Documentos', 'Estado', 'Usuario', 'Acciones']);
$paginator = new \sowerphp\app\View_Helper_Paginator([
    'link' => $_base.'/dte/dte_intercambios/listar',
    'filter' => false,
]);
$paginator->setColsWidth([null, null, null, null, null, null, null, 100]);
echo $paginator->generate($intercambios, $pages, $p);
