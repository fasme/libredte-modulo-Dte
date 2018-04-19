<div class="page-header"><h1>Mantenedor de folios empresa <?=$Emisor->razon_social?></h1></div>
<p>Aquí podrá administrar los códigos de autorización de folios (CAF) disponibles para la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($folios as &$f) {
    $acciones = '<a href="dte_folios/ver/'.$f['dte'].'" title="Ver mantenedor del folio tipo '.$f['dte'].'"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="dte_folios/modificar/'.$f['dte'].'" title="Editar folios de tipo '.$f['dte'].'"><span class="fa fa-edit btn btn-default"></span></a>';
    $f[] = $acciones;
}
array_unshift($folios, ['Código', 'Documento', 'Siguiente folio', 'Total disponibles', 'Alertar', 'Acciones']);
new \sowerphp\general\View_Helper_Table($folios);
?>
<div class="row">
    <div class="col-xs-4">
        <a class="btn btn-default btn-lg btn-block" href="dte_folios/agregar" role="button">
            <span class="fa fa-edit"></span>
            Crear mantenedor de folio
        </a>
    </div>
    <div class="col-xs-4">
        <a class="btn btn-default btn-lg btn-block <?=!\sowerphp\core\Configure::read('proveedores.api.libredte')?'disabled':''?>" href="dte_folios/solicitar_caf" role="button">
            <span class="fa fa-download"></span>
            Solicitar CAF
        </a>
    </div>
    <div class="col-xs-4">
        <a class="btn btn-default btn-lg btn-block" href="dte_folios/subir_caf" role="button">
            <span class="fa fa-upload"></span>
            Subir archivo CAF
        </a>
    </div>
</div>
