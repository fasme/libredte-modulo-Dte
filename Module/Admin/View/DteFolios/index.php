<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="dte_folios/agregar" class="nav-link">
            <i class="fa fa-edit"></i> Crear
        </a>
    </li>
    <li class="nav-item">
        <a href="dte_folios/solicitar_caf" class="nav-link">
            <i class="fa fa-download"></i> Solicitar CAF
        </a>
    </li>
    <li class="nav-item">
        <a href="dte_folios/subir_caf" class="nav-link">
            <i class="fa fa-upload"></i> Subir CAF
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/informe_estados" title="Generar informe con el estado del SII para los folios" class="nav-link">
            <i class="far fa-file"></i> Estado de folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Mantenedor de folios <small><?=$Emisor->getNombre()?></small></h1></div>
<p>Aquí podrá administrar los códigos de autorización de folios (CAF) disponibles para la empresa <?=$Emisor->getNombre()?>.</p>
<?php
foreach ($folios as &$f) {
    $acciones = '<a href="dte_folios/ver/'.$f['dte'].'" title="Ver mantenedor del folio tipo '.$f['dte'].'" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="dte_folios/modificar/'.$f['dte'].'" title="Editar folios de tipo '.$f['dte'].'" class="btn btn-primary"><i class="fa fa-edit fa-fw"></i></a>';
    $f[] = $acciones;
}
array_unshift($folios, ['Código', 'Documento', 'Siguiente folio', 'Total disponibles', 'Alertar', 'Acciones']);
new \sowerphp\general\View_Helper_Table($folios);
?>
<div class="card mt-4">
    <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Para qué es la alerta de folios?</div>
    <div class="card-body">
        <p>Se usa cuando la cantidad de folios llega al valor de la alerta, permitiendo realizar timbraje automático o bien informar al administrador por correo que se alcanzó la alerta y quedan pocos folios.</p>
        <p>Si se tiene configurado el timbraje automático, el sistema tratará de timbrar de manera automática según la siguiente fórmula:</p>
        <p class="text-monospace">folios a timbrar = alerta x multiplicador</p>
        <p>El multiplicador se define en la configuración de la empresa, en la pestaña <em>Facturación</em>.</p>
    </div>
</div>
