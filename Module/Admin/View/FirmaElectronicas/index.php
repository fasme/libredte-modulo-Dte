<div class="page-header"><h1>Mantenedor firmas electrónicas <small><?=$Emisor->razon_social?></small></h1></div>
<p>A continuación se muestra un listado de los usuarios autorizados a operar con la empresa <?=$Emisor->razon_social?> y que tienen firma electrónica registrada en el sistema.</p>
<?php
foreach ($firmas as &$f) {
    $f['desde'] = \sowerphp\general\Utility_Date::format($f['desde'], 'd/m/Y H:i');
    $f['hasta'] = \sowerphp\general\Utility_Date::format($f['hasta'], 'd/m/Y H:i');
    $f['administrador'] = $f['administrador'] ? 'si' : 'no';
}
array_unshift($firmas, ['RUN', 'Nombre', 'Email', 'Válida desde', 'Válida hasta', 'Emisor', 'Usuario', 'Administrador']);
new \sowerphp\general\View_Helper_Table($firmas);
?>
<div class="row">
    <div class="col-sm-4">
        <a class="btn btn-primary btn-lg btn-block" href="firma_electronicas/agregar" role="button">
            <span class="fa fa-edit"></span>
            Agregar mi firma electrónica
        </a>
    </div>
    <div class="col-sm-4">
        <a class="btn btn-primary btn-lg btn-block" href="firma_electronicas/descargar" role="button">
            <span class="fa fa-download"></span>
            Descargar mi firma electrónica
        </a>
    </div>
    <div class="col-sm-4">
        <a class="btn btn-primary btn-lg btn-block" href="firma_electronicas/eliminar" role="button">
            <span class="fas fa-times"></span>
            Eliminar mi firma electrónica
        </a>
    </div>
</div>
