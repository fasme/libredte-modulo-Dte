<ul class="nav nav-pills pull-right">
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fas fa-university"></span> Recibidos en SII <span class="caret"></span>
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
<?php if (!$Emisor->config_ambiente_en_certificacion) : ?>
    <li>
        <a href="https://www4.sii.cl/consdcvinternetui/#/home" title="Ir al registro de compra y venta en el SII" target="_blank">
            <span class="fas fa-university"></span>
            Ver RCV en SII
        </a>
    </li>
<?php endif; ?>
<?php if ($soloPendientes) : ?>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/listar" title="Listar todos los intercambios paginados">
            <span class="fa fa-list-alt"></span>
            Listar todo
        </a>
    </li>
<?php else : ?>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/listar/0/1" title="Ver todos los documentos pendientes de procesar">
            <span class="fa fa-list-alt"></span>
            Pendientes
        </a>
    </li>
<?php endif; ?>
    <li>
        <a href="<?=$_base?>/dte/dte_intercambios/pendientes" title="Descargar los documentos pendientes de procesar">
            <span class="fa fa-download"></span>
            Descargar
        </a>
    </li>
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fas fa-sync"></span> Actualizar <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li><a href="<?=$_base?>/dte/dte_intercambios/actualizar/7">Últimos 7 días</a></li>
            <li><a href="<?=$_base?>/dte/dte_intercambios/actualizar/30">Últimos 30 días</a></li>
            <li><a href="<?=$_base?>/dte/dte_intercambios/actualizar/90">Últimos 90 días</a></li>
        </ul>
    </li>
</ul>

<div class="page-header"><h1>Bandeja de intercambio</h1></div>
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
    $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$i['codigo'].'" title="Descargar PDF del intercambio"><span class="far fa-file-pdf btn btn-default"></span></a>';
    $i[] = $acciones;
    if (is_numeric($i['emisor'])) {
        $i['emisor'] = \sowerphp\app\Utility_Rut::addDV($i['emisor']);
    }
}
array_unshift($intercambios, [
    '',
    '<input type="text" name="emisor" class="check integer form-control" placeholder="RUT o razón social emisor" autofocus="autofocus" />',
    '',
    '',
    '<input type="text" name="folio" class="check integer form-control" placeholder="Folio del DTE" />',
    '',
    '',
    '<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>',
]);
array_unshift($intercambios, ['Código', 'Emisor', 'Firmado', 'Recibido', 'Documentos', 'Estado', 'Usuario', 'Acciones']);
$paginator = new \sowerphp\app\View_Helper_Paginator([
    'link' => $_base.'/dte/dte_intercambios/listar',
]);
$paginator->setColsWidth([null, null, null, null, null, null, null, 100]);
echo $paginator->generate($intercambios, $pages, $p);
