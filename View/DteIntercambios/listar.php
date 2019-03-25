<ul class="nav nav-pills float-right">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-university"></i> Recibidos en SII
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_compras/registro_compras" title="Ir al registro de compra del SII" class="dropdown-item">
                Registro de compras
            </a>
            <a href="<?=$_base?>/dte/dte_recibidos/bhe" title="Buscar las boletas de honorarios electrónicas recibidas en el SII" class="dropdown-item">
                Boletas honorarios en SII
            </a>
        </div>
    </li>
<?php if ($soloPendientes) : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/listar" title="Listar todos los intercambios paginados" class="nav-link">
            <i class="fa fa-list-alt"></i>
            Listar todo
        </a>
    </li>
<?php else : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/listar/0/1" title="Ver todos los documentos pendientes de procesar" class="nav-link">
            <i class="fa fa-list-alt"></i>
            Pendientes
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/pendientes" title="Descargar los documentos pendientes de procesar" class="nav-link">
            <i class="fa fa-download"></i>
            Descargar
        </a>
    </li>
    <li class="nav-item" class="dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-sync"></i> Actualizar
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="<?=$_base?>/dte/dte_intercambios/actualizar/3" class="dropdown-item">Últimos 3 días</a>
            <a href="<?=$_base?>/dte/dte_intercambios/actualizar/7" class="dropdown-item">Última semana</a>
            <a href="<?=$_base?>/dte/dte_intercambios/actualizar/14" class="dropdown-item">Últimas 2 semanas</a>
            <a href="<?=$_base?>/dte/dte_intercambios/actualizar/30" class="dropdown-item">Último mes</a>
            <a href="<?=$_base?>/dte/dte_intercambios/actualizar/90" class="dropdown-item">Últimos 3 meses</a>
        </div>
    </li>
</ul>

<div class="page-header"><h1>Bandeja de intercambio</h1></div>
<p>Aquí podrá revisar, aceptar o rechazar aquellos documentos que otros contribuyentes han envíado a <?=$Emisor->razon_social?> de manera electrónica.</p>

<?php
foreach ($intercambios as &$i) {
    $i['documentos'] = is_array($i['documentos']) ? implode('<br/>', $i['documentos']) : num($i['documentos']);
    if ($soloPendientes) {
        $i[] = '';
        $i[] = '';
    }
    $i['fecha_hora_firma'] = \sowerphp\general\Utility_Date::format($i['fecha_hora_firma']);
    $i['fecha_hora_email'] = \sowerphp\general\Utility_Date::format($i['fecha_hora_email']);
    $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$i['codigo'].'" title="Ver detalles del intercambio" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$i['codigo'].'" title="Descargar PDF del intercambio" class="btn btn-primary"><i class="far fa-file-pdf fa-fw"></i></a>';
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
    '<button type="submit" class="btn btn-primary"><i class="fa fa-search fa-fw" aria-hidden="true"></i></button>',
]);
array_unshift($intercambios, ['Código', 'Emisor', 'Firmado', 'Recibido', 'Documentos', 'Estado', 'Procesado', 'Acciones']);
$paginator = new \sowerphp\app\View_Helper_Paginator([
    'link' => $_base.'/dte/dte_intercambios/listar',
]);
$paginator->setColsWidth([null, null, null, null, null, null, null, 110]);
echo $paginator->generate($intercambios, $pages, $p);
?>

<div class="card mt-4">
    <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Por qué no están todos los documentos de mis proveedores en esta bandeja?</div>
    <div class="card-body">
        <p>Los documentos que aquí aparecen son todos los XML del tipo <span class="text-monospace">EnvioDTE</span> que han sido enviados a su correo de intercambio. Si un proveedor no ha enviado el XML no tendrá el documento en la bandeja y por lo tanto no lo tendrá enlazado al documento recibido, ni tampoco su PDF.</p>
        <p>Si falta algún documento debe pedir a su proveedor que envíe el XML a su correo de intercambio: <span class="lead text-center text-monospace"><?=$Emisor->config_email_intercambio_user?></span></p>
    </div>
</div>
