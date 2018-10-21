<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas" title="Ir al libro de ventas (IEV)" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de ventas
        </a>
    </li>
</ul>
<div class="page-header"><h1>Registro de ventas del SII</small></h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'periodo',
    'label' => 'Período',
    'check' => 'notempty integer',
    'value' => date('Ym'),
    'placeholder' => date('Ym'),
]);
echo $f->end('Ver resumen del período');
