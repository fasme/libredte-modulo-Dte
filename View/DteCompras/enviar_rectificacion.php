<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_compras/ver/<?=$periodo?>" title="Volver a la IEC del período <?=$periodo?>">
            Volver a la IEC <?=$periodo?>
        </a>
    </li>
</ul>
<script>
function get_codigo_reemplazo() {
    $.get(_base+'/api/dte/dte_compras/codigo_reemplazo/<?=$periodo?>/<?=$Emisor->rut?>', function(codigo) {
        document.getElementById('CodAutRecField').value = codigo;
    }).fail(function(error){alert(error.responseJSON)});
}
</script>
<h1>Rectificación IEC para el período <?=$periodo?></h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action' => $_base.'/dte/dte_compras/enviar_sii/'.$periodo,
    'onsubmit'=>'Form.check() && Form.checkSend(\'¿Está seguro de enviar la rectificación del libro?\')'
]);
echo $f->input([
    'name' => 'CodAutRec',
    'label'=>'Autorización rectificación',
    'help' => 'Código de autorización de rectificación obtenido desde el SII <a href="#" onclick="get_codigo_reemplazo()">[solicitar código aquí]</a>',
    'check'=>'notempty',
]);
echo $f->end('Enviar rectificación');
