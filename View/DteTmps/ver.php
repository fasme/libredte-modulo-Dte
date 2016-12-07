<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_tmps" title="Volver a los documentos temporales">
            Volver a documentos temporales
        </a>
    </li>
</ul>

<h1>Documento temporal <?=$DteTmp->getFolio()?></h1>
<p>Esta es la página del documento temporal <?=$DteTmp->getTipo()->tipo?> folio <?=$DteTmp->getFolio()?> de la empresa <?=$Emisor->razon_social?> emitido a <?=$Receptor->razon_social?> (<?=$Receptor->rut.'-'.$Receptor->dv?>).</p>

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
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos básicos</a></li>
        <li role="presentation"><a href="#actualizar_fecha" aria-controls="actualizar_fecha" role="tab" data-toggle="tab">Actualizar fecha</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Documento', 'Folio', 'Fecha', 'Receptor', 'Total'],
    [$DteTmp->getTipo()->tipo, $DteTmp->getFolio(), \sowerphp\general\Utility_Date::format($DteTmp->fecha), $Receptor->razon_social, num($DteTmp->total)],
]);
?>
    <div class="row">
        <div class="col-md-3">
            <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/cotizacion/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <span class="fa fa-dollar" style="font-size:24px"></span>
                Cotización
            </a>
        </div>
        <div class="col-md-3">
            <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/pdf/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
                Previsualización
            </a>
        </div>
        <div class="col-md-3">
            <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/xml/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <span class="fa fa-file-code-o" style="font-size:24px"></span>
                XML sin firmar
            </a>
        </div>
        <div class="col-md-3">
            <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/json/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <span class="fa fa-file-code-o" style="font-size:24px"></span>
                Archivo JSON
            </a>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-md-6">
            <a class="btn btn-success btn-lg btn-block" href="<?=$_base?>/dte/documentos/generar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button" onclick="return Form.checkSend('Confirmar la generación del DTE real')">Generar DTE real</a>
        </div>
        <div class="col-md-6">
            <a class="btn btn-danger btn-lg btn-block" href="<?=$_base?>/dte/dte_tmps/eliminar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" title="Eliminar documento" onclick="return Form.checkSend('Confirmar la eliminación del documento temporal')">Eliminar documento</a>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO ACTUALIZAR FECHA -->
<div role="tabpanel" class="tab-pane" id="actualizar_fecha">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action' => $_base.'/dte/dte_tmps/actualizar/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo,
    'id' => 'actualizarFechaForm',
    'onsubmit' => 'Form.check(\'actualizarFechaForm\')'
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha',
    'value' => date('Y-m-d'),
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'actualizar_precios',
    'label' => '¿Actualizar precios?',
    'options' => ['No', 'Si'],
    'value' => 1,
    'help' => 'Si el documento tiene items codificados y sus precios no están en pesos (CLP) entonces se pueden actualizar sus valores',
]);
echo $f->end('Actualizar fecha');
?>
</div>
<!-- FIN ACTUALIZAR FECHA -->

    </div>
</div>
