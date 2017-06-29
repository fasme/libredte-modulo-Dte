<h1>Usuarios empresa <?=$Contribuyente->razon_social?></h1>
<p>Aquí podrá modificar los usuarios autorizados a operar con la empresa <?=$Contribuyente->razon_social?> RUT <?=num($Contribuyente->rut).'-'.$Contribuyente->dv?>, para la cual usted es el usuario administrador.</p>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
    }
});
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#usuarios" aria-controls="usuarios" role="tab" data-toggle="tab">Usuarios autorizados</a></li>
        <li role="presentation"><a href="#dtes" aria-controls="dtes" role="tab" data-toggle="tab">Documentos por usuario</a></li>
        <li role="presentation"><a href="#administrador" aria-controls="administrador" role="tab" data-toggle="tab">Administrador</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO USUARIOS AUTORIZADOS -->
<div role="tabpanel" class="tab-pane active" id="usuarios">
<?php
// inputs y ayuda
$inputs = [['name'=>'usuario', 'check'=>'notempty']];
$permisos_ayuda = '<ul>';
foreach ($permisos_usuarios as $permiso => $info) {
    $permisos_ayuda .= '<li><strong>'.$permiso.'</strong>: '.$info['nombre'].' <small>('.$info['descripcion'].')</small>'.'</li>';
    $inputs[] = ['type'=>'select', 'name'=>'permiso_'.$permiso, 'options'=>['No', 'Si']];
}
$permisos_ayuda .= '</ul>';
// usuarios y sus permisos
$usuarios = [];
foreach ($Contribuyente->getUsuarios() as $u => $p) {
    $permisos = [];
    foreach ($permisos_usuarios as $permiso => $info) {
        $permisos['permiso_'.$permiso] = (int)in_array($permiso, $p);
    }
    $usuarios[] = array_merge(['usuario'=>$u], $permisos);
}
// mantenedor usuarios
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'id' => 'usuarios',
    'onsubmit' => 'Form.check(\'usuarios\') && Form.checkSend()',
]);
$f->setStyle(false);
echo $f->input([
    'type' => 'js',
    'id' => 'usuarios',
    'label' => 'Usuarios autorizados',
    'titles' => array_merge(['Usuario'], array_keys($permisos_usuarios)),
    'inputs' => $inputs,
    'values' => $usuarios,
]);
$f->setStyle('horizontal');
echo $f->end('Modificar usuarios autorizados');
?>
<div class="well">
<p>Debe ingresar el nombre del usuario que desea autorizar y alguno de los permisos:</p>
<?=$permisos_ayuda,"\n"?>
</div>
</div>
<!-- FIN USUARIOS AUTORIZADOS -->

<!-- INICIO DOCUMENTOS POR USUARIOS -->
<div role="tabpanel" class="tab-pane" id="dtes">
<p>Aquí puede restringir los documentos que un usuario puede emitir. Por defecto, si todo está en "No", se permite cualquier documento que la empresa tenga autorizado. Para limitar los documentos, seleccionar a lo menos uno.</p>
<?php
echo $f->begin([
    'action' => '../usuarios_dtes/'.$Contribuyente->rut,
    'id' => 'usuarios_dtes',
    'onsubmit' => 'Form.check(\'usuarios_dtes\')',
]);
$usuarios_dtes = [];
$aux = $Contribuyente->getDocumentosAutorizados();
$documentos_autorizados = [];
foreach ($aux as $d) {
    $documentos_autorizados[$d['codigo']] = $d['tipo'];
}
$inputs = [['name'=>'usuario', 'check'=>'notempty', 'attr'=>'readonly="readonly"']];
foreach ($documentos_autorizados as $codigo => $tipo) {
    $inputs[] = ['type'=>'select', 'name'=>'dte_'.$codigo, 'options'=>['No', 'Si']];
}
$autorizados = $Contribuyente->getDocumentosAutorizadosPorUsuario();
foreach ($Contribuyente->getUsuarios() as $u => $p) {
    $documentos = [];
    foreach ($documentos_autorizados as $codigo => $tipo) {
        if (!empty($autorizados[$u])) {
            $documentos['dte_'.$codigo] = (int)in_array($codigo, $autorizados[$u]);
        } else {
            $documentos['dte_'.$codigo] = 0;
        }
    }
    $usuarios_dtes[] = array_merge(['usuario'=>$u], $documentos);
}
$f = new \sowerphp\general\View_Helper_Form();
$f->setStyle(false);
echo $f->input([
    'type' => 'table',
    'id' => 'usuarios_dtes',
    'label' => 'DTEs x usuarios',
    'titles' => array_merge(['Usuario'], array_keys($documentos_autorizados)),
    'inputs' => $inputs,
    'values' => $usuarios_dtes,
]);
$f->setStyle('horizontal');
echo $f->end('Guardar documentos por usuarios');
?>
<div class="well">
<p>Documentos que la empresa tiene autorizados en LibreDTE:</p>
<ul>
<?php foreach ($documentos_autorizados as $codigo => $tipo) : ?>
    <li><strong><?=$codigo?></strong>: <?=$tipo?></li>
<?php endforeach; ?>
</ul>
</div>
</div>
<!-- FIN DOCUMENTOS POR USUARIOS -->

<!-- INICIO ADMINISTRADOR -->
<div role="tabpanel" class="tab-pane" id="administrador">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action' => '../transferir/'.$Contribuyente->rut,
    'id' => 'transferir',
    'onsubmit' => 'Form.check(\'transferir\') && Form.checkSend(\'¿Está seguro de querer transferir la empresa al nuevo usuario?\')',
]);
echo $f->input([
    'name' => 'usuario',
    'label' => 'Administrador',
    'value' => $Contribuyente->getUsuario()->usuario,
    'check' => 'notempty',
    'help' => 'Usuario que actúa como administrador de la empresa en LibreDTE',
]);
echo $f->end('Cambiar usuario administrador');
?>
</div>
<!-- FIN ADMINISTRADOR -->

    </div>
</div>
