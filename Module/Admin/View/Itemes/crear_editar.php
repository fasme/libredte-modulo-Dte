<?php if (isset($Obj)) : ?>
<ul class="nav nav-pills pull-right">
<?php if (\sowerphp\core\Module::loaded('Inventario')) : ?>
    <li>
        <a href="<?=$_base?>/inventario/inventario_itemes/editar/<?=$Obj->codigo?>/<?=$Obj->codigo_tipo?>" title="Editar item en el inventario">
            <span class="fa fa-cubes"></span> Editar en inventario
        </a>
    </li>
<?php endif; ?>
<?php if (\sowerphp\core\Module::loaded('Tienda')) : ?>
    <li>
        <a href="<?=$_base?>/tienda/admin/tienda_itemes/editar/<?=$Obj->codigo?>/<?=$Obj->codigo_tipo?>" title="Editar item la tienda">
            <span class="fa fa-shopping-cart"></span> Editar en la tienda
        </a>
    </li>
<?php endif; ?>
</ul>
<?php endif; ?>
<h1><?=$accion?> producto o servicio</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form ();
echo $f->begin(array('onsubmit'=>'Form.check()'));
echo $f->input([
    'name' => 'codigo_tipo',
    'label' => 'Tipo de código',
    'value' => isset($Obj)?$Obj->codigo_tipo:'INT1',
    'check' => 'notempty',
    'attr' => isset($Obj)?'disabled="disabled"':'maxlength="10"',
    'help' => $Contribuyente->config_extra_agente_retenedor?'Si es agente retenedor del producto debe utilizar el tipo de código CPCS':false,
]);
$help = 'Si es agente retenedor del producto debe utilizar el código, nombre y unidad descritos en la tabla anexa del <a href="http://www.sii.cl/factura_electronica/formato_retenedores.pdf" target="_blank">formato de retenedores</a>';
echo $f->input([
    'name' => 'codigo',
    'label' => 'Código',
    'value' => isset($Obj)?$Obj->codigo:'',
    'check' => 'notempty',
    'attr' => isset($Obj)?'disabled="disabled"':'maxlength="35" onblur="this.value=this.value.replace(\'/\', \'_\')"',
    'help' => $Contribuyente->config_extra_agente_retenedor?$help:false,
]);
echo $f->input([
    'name' => 'item',
    'label' => 'Nombre',
    'value' => isset($Obj)?$Obj->item:'',
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
    'help' => $Contribuyente->config_extra_agente_retenedor?$help:false,
]);
echo $f->input([
    'name' => 'descripcion',
    'label' => 'Descripción',
    'value' => isset($Obj)?$Obj->descripcion:'',
    'attr' => 'maxlength="1000"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'clasificacion',
    'label' => 'Clasificación',
    'options' => [''=>'Seleccionar clasificación'] + $clasificaciones,
    'value' => isset($Obj)?$Obj->clasificacion:'',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'unidad',
    'label' => 'Unidad',
    'value' => isset($Obj)?$Obj->unidad:'',
    'attr' => 'maxlength="4"',
    'help' => $Contribuyente->config_extra_agente_retenedor?$help:false,
]);
echo $f->input([
    'name' => 'precio',
    'label' => 'Precio',
    'value' => isset($Obj)?$Obj->precio:'',
    'check' => 'notempty real',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'moneda',
    'label' => 'Moneda',
    'options' => ['CLP'=>'Pesos', 'CLF'=>'UF', 'USD'=>'Dólares', 'EUR'=>'Euros'],
    'value' => isset($Obj)?$Obj->moneda:'CLP',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'bruto',
    'label' => 'Tipo precio',
    'options' => ['Neto', 'Bruto'],
    'value' => isset($Obj)?$Obj->bruto:false,
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'exento',
    'label' => '¿Exento?',
    'options' => ['No', 'Si'],
    'value' => isset($Obj)?$Obj->exento:0,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'descuento',
    'label' => 'Descuento',
    'value' => isset($Obj)?$Obj->descuento:0,
    'check' => 'notempty real',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'descuento_tipo',
    'label' => 'Tipo descuento',
    'options' => ['%'=>'%', '$'=>'$'],
    'value' => isset($Obj)?$Obj->descuento_tipo:'%',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_adicional',
    'label' => 'Impuesto adicional',
    'options' => [''=>'Sin impuesto adicional'] + $impuesto_adicionales,
    'value' => isset($Obj)?$Obj->impuesto_adicional:'',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'activo',
    'label' => '¿Activo?',
    'options' => ['No', 'Si'],
    'value' => isset($Obj)?$Obj->activo:1,
    'check' => 'notempty',
]);
echo $f->end('Guardar');
?>
<div style="float:left;color:red">* campo es obligatorio</div>
<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base.$listarUrl?>">Volver al listado de registros</a>
</div>
