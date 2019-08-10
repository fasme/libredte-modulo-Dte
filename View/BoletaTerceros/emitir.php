<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/boleta_terceros" title="Ver boletas de terceros emitidas por cada período" class="nav-link">
            <i class="fas fa-user-secret"></i>
            Boletas de terceros
        </a>
    </li>
</ul>
<div class="page-header"><h1>Emitir boleta de terceros</h1></div>
<div class="alert alert-info text-center lead">
    ¿Quieres emitir boletas de terceros desde LibreDTE?<br/>
    <a href="https://libredte.cl/soporte?asunto=<?=urlencode('Emisión BTE desde LibreDTE')?>">Contáctanos</a> si estás interesado en que habilitemos esta opción
</div>
<?php
/*$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'rut',
    'label' => 'RUT receptor',
    'check' => 'notempty rut',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha',
    'value' => date('Y-m-d'),
    'check' => 'notempty date',
]);
echo $f->end('Emitir boleta');
*/
