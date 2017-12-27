<h1>Mantenedor firmas electrónicas <?=$Emisor->razon_social?></h1>
<p>A continuación se muestra un listado de los usuarios autorizados a operar con la empresa <?=$Emisor->razon_social?> y que tienen firma electrónica registrada en el sistema.</p>
<?php
foreach ($firmas as &$f) {
    $f['administrador'] = $f['administrador'] ? 'si' : 'no';
}
array_unshift($firmas, ['RUN', 'Nombre', 'Email', 'Válida desde', 'Válida hasta', 'Emisor', 'Usuario', 'Administrador']);
new \sowerphp\general\View_Helper_Table($firmas);
?>
<div class="row">
    <div class="col-xs-4">
        <a class="btn btn-default btn-lg btn-block" href="firma_electronicas/agregar" role="button">
            <span class="fa fa-edit"></span>
            Agregar mi firma electrónica
        </a>
    </div>
    <div class="col-xs-4">
        <a class="btn btn-default btn-lg btn-block" href="firma_electronicas/descargar" role="button">
            <span class="fa fa-download"></span>
            Descargar mi firma electrónica
        </a>
    </div>
    <div class="col-xs-4">
        <a class="btn btn-default btn-lg btn-block" href="firma_electronicas/eliminar" role="button">
            <span class="fas fa-times"></span>
            Eliminar mi firma electrónica
        </a>
    </div>
</div>
