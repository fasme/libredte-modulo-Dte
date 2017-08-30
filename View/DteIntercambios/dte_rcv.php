<div class="page-header"><h1>Ingresar acción al registro compra/venta SII</h1></div>
<ul>
    <li><strong>Emisor</strong>: <?=$Emisor->razon_social?></li>
    <li><strong>Documento</strong>: <?=$DteTipo->tipo?></li>
    <li><strong>Folio</strong>: <?=$folio?></li>
    <li><strong>Fecha recepción SII</strong>: <?=\sowerphp\general\Utility_Date::format($fecha_recepcion, 'd/m/Y H:i')?></li>
    <li>
        <strong>Eventos</strong>:
        <ul>
<?php foreach ($eventos as $e) : ?>
            <li><?=$e['glosa']?>, registrado por <?=$e['responsable']?> el <?=\sowerphp\general\Utility_Date::format($e['fecha'], 'd/m/Y H:i')?></li>
<?php endforeach; ?>
        </ul>
    </li>
    <li><strong>Datos para cesión</strong>: <?=$cedible['glosa']?></li>
</ul>
<hr/>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin();
echo $f->input([
    'type' => 'select',
    'name' => 'accion',
    'label' => 'Acción',
    'options' => \sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones,
    'check' => 'notempty',
    'help' => 'Si rechaza un DTE no podrá aceptarlo en el futuro. Si acepta un DTE no podrá rechazarlo en el futuro.'
]);
echo $f->end('Ingresar acción al registro');
