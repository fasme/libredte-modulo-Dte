<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_guias" title="Volver a libro de guías de despacho">
            Volver a libro guías
        </a>
    </li>
</ul>
<div class="page-header"><h1>Enviar libro de guías de despacho sin movimientos</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.checkSend(\'¿Está seguro de enviar el libro sin movimientos?\')']);
echo $f->input(['name'=>'periodo', 'label'=>'Período', 'placeholder'=>date('Ym'), 'help'=>'Período en formato AAAAMM, ejemplo: '.date('Ym'), 'check'=>'notempty integer']);
echo $f->end('Enviar libro sin movimientos');
