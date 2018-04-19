<div class="page-header"><h1><?=$accion?> clasificación de items</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form ();
echo $f->begin(array('onsubmit'=>'Form.check()'));
echo $f->input([
    'name' => 'codigo',
    'label' => 'Código',
    'value' => isset($Obj)?$Obj->codigo:'',
    'check' => 'notempty',
    'attr' => isset($Obj)?'disabled="disabled"':'maxlength="35"',
]);
echo $f->input([
    'name' => 'clasificacion',
    'label' => 'Glosa',
    'value' => isset($Obj)?$Obj->clasificacion:'',
    'check' => 'notempty',
    'attr' => 'maxlength="50"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'superior',
    'label' => 'Superior',
    'options' => [''=>'Sin categoría superior'] + $clasificaciones,
    'value' => isset($Obj)?$Obj->superior:'',
    'help' => 'Categoría a la que pertenece esta',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'activa',
    'label' => '¿Activa?',
    'options' => ['No', 'Si'],
    'value' => isset($Obj)?$Obj->activa:1,
    'check' => 'notempty',
]);
echo $f->end('Guardar');
?>
<div style="float:left;color:red">* campo es obligatorio</div>
<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base.$listarUrl?>">Volver al listado de registros</a>
</div>
