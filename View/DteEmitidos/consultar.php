<div class="page-header"><h1>Consultar documento tributario electrónico (DTE)</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['focus'=>'emisorField', 'onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'emisor',
    'label' => 'RUT emisor',
    'check' => 'notempty rut',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Tipo DTE',
    'options' => [''=>'Seleccionar un tipo de documento'] + $dtes,
    'value' => $dte,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'folio',
    'label' => 'Folio DTE',
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha emisión',
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'total',
    'label' => 'Monto total',
    'check' => 'notempty integer',
]);
echo $f->end('Consultar DTE');

// si se encontró un DTE se muestra
if (isset($DteEmitido)) : ?>
<h2><?=$DteEmitido->getTipo()->tipo?> #<?=$DteEmitido->folio?> a <?=\sowerphp\app\Utility_Rut::addDV($DteEmitido->receptor)?></h2>
<div class="row">
    <div class="col-md-<?=$DteEmitido->track_id?9:12?>">
<?php
    new \sowerphp\general\View_Helper_Table([
    ['Documento', 'Folio', 'Receptor', 'Exento', 'Neto', 'IVA', 'Total'],
    [$DteEmitido->getTipo()->tipo, $DteEmitido->folio, $DteEmitido->getReceptor()->razon_social, num($DteEmitido->exento), num($DteEmitido->neto), num($DteEmitido->iva), num($DteEmitido->total)],
]);
?>
        <div class="row">
            <div class="col-md-6">
                <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/pdf/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/0/<?=$DteEmitido->emisor?>/<?=$DteEmitido->fecha?>/<?=$DteEmitido->total?>" role="button">
                    <span class="far fa-file-pdf" style="font-size:24px"></span>
                    Descargar PDF del DTE
                </a>
            </div>
            <div class="col-md-6">
                <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/xml/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$DteEmitido->emisor?>/<?=$DteEmitido->fecha?>/<?=$DteEmitido->total?>" role="button">
                    <span class="far fa-file-code" style="font-size:24px"></span>
                    Descargar XML del DTE
                </a>
            </div>
        </div>
    </div>
<?php if ($DteEmitido->track_id) : ?>
    <div class="col-md-3 center bg-info" style="padding:1em">
        <span class="lead">Track ID SII: <?=$DteEmitido->track_id?></span>
        <p><strong><?=$DteEmitido->revision_estado?></strong></p>
        <p><?=$DteEmitido->revision_detalle?></p>
    </div>
<?php endif; ?>
</div>

<?php if (\sowerphp\core\Module::loaded('Pagos') and $DteEmitido->getEmisor()->config_pagos_habilitado) : ?>
<div class="row" style="margin-top:2em">
    <a class="btn btn-info btn-lg btn-block" href="<?=$_url.'/pagos/documentos/pagar/'.$DteEmitido->dte.'/'.$DteEmitido->folio.'/'.$DteEmitido->emisor.'/'.$DteEmitido->fecha.'/'.$DteEmitido->total?>" role="button">
        Ir a la página de pago del documento
    </a>
</div>
<?php endif; ?>

<?php endif; ?>
