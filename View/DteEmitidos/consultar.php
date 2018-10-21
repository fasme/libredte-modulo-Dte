<script type="text/javascript" src="<?=$_base?>/js/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?=$_base?>/js/datepicker/bootstrap-datepicker.es.js"></script>
<link rel="stylesheet" href="<?=$_base?>/js/datepicker/datepicker3.css" />
<div class="text-center mt-4 mb-4">
    <a href="<?=$_base?>/"><img src="<?=$_base?>/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 200px" /></a>
</div>
<div class="row">
    <div class="offset-md-3 col-md-6">
<?php
$message = \sowerphp\core\Model_Datasource_Session::message();
if ($message) {
    $icons = [
        'success' => 'ok',
        'info' => 'info-sign',
        'warning' => 'warning-sign',
        'danger' => 'exclamation-sign',
    ];
    echo '<div class="alert alert-',$message['type'],'" role="alert">',"\n";
    echo '    <span class="glyphicon glyphicon-',$icons[$message['type']],'" aria-hidden="true"></span>',"\n";
    echo '    <span class="sr-only">',$message['type'],': </span>',$message['text'],"\n";
    echo '    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="Cerrar">&times;</a>',"\n";
    echo '</div>'."\n";
}
?>
<?php if (!isset($DteEmitido)) : ?>
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="text-center mb-4">Consultar DTE</h1>
                <form action="<?=$_base.$_request?>" method="post" onsubmit="return Form.check()" class="mb-4">
                    <div class="form-group">
                        <label for="emisor" class="sr-only">RUT emisor</label>
                        <input type="text" name="emisor" id="emisor" class="form-control form-control-lg check rut" required="required" placeholder="RUT emisor">
                    </div>
                    <div class="form-group">
                        <label for="dte" class="sr-only">Tipo DTE</label>
                        <select name="dte" id="dte" class="form-control form-control-lg" required="required">
                            <option value="">Seleccionar tipo de DTE</option>
<?php foreach ($dtes as $d) : ?>
                            <option value="<?=$d['codigo']?>"<?=($d['codigo']==$dte?' selected="selected"':'')?>><?=$d['glosa']?></option>
<?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="folio" class="sr-only">Folio del DTE</label>
                        <input type="number" name="folio" id="folio" class="form-control form-control-lg" required="required" placeholder="Folio del DTE">
                    </div>
                    <div class="form-group">
                        <label for="fecha" class="sr-only">Fecha de emisión</label>
                        <input type="text" name="fecha" id="fecha" class="form-control form-control-lg" required="required" placeholder="Fecha de emisión">
                        <script>
                            $(function() {
                                $("#fecha").datepicker({"format":"yyyy-mm-dd","weekStart":1,"todayBtn":"linked","language":"es","todayHighlight":true,"orientation":"auto"});
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label for="total" class="sr-only">Monto total</label>
                        <input type="number" name="total" id="total" class="form-control form-control-lg" required="required" placeholder="Monto total">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Buscar documento</button>
                </form>
                <script> $(function() { $("#emisor").focus(); }); </script>
            </div>
        </div>
<?php else: ?>
<?php $links = $DteEmitido->getLinks(); ?>
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="text-center mb-4"><?=$DteEmitido->getTipo()->tipo?> #<?=$DteEmitido->folio?><br/><small><?=$DteEmitido->getEmisor()->getNombre()?></small></h1>
<?php
    new \sowerphp\general\View_Helper_Table([
    ['Receptor', 'Exento', 'Neto', 'IVA', 'Total'],
    [$DteEmitido->getReceptor()->razon_social, num($DteEmitido->exento), num($DteEmitido->neto), num($DteEmitido->iva), num($DteEmitido->total)],
]);
?>
<?php if ($DteEmitido->track_id) : ?>
    <div class="text-center">
        <p>
            <span class="lead">Track ID SII: <?=$DteEmitido->track_id?></span><br/>
            <strong><?=$DteEmitido->revision_estado?></strong><br/>
            <?=$DteEmitido->revision_detalle?>
        </p>
    </div>
<?php endif; ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a class="btn btn-primary btn-lg btn-block" href="<?=$links['pdf']?>" role="button">
                            <span class="far fa-file-pdf"></span>
                            Descargar PDF
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a class="btn btn-primary btn-lg btn-block" href="<?=$links['xml']?>" role="button">
                            <span class="far fa-file-code"></span>
                            Descargar XML
                        </a>
                    </div>
                </div>
<?php if (!empty($links['pagar'])) : ?>
                <div class="row">
                    <div class="col-sm-12">
                        <a class="btn btn-success btn-lg btn-block" href="<?=$links['pagar']?>" role="button">
                            Ir a la página de pago del documento
                        </a>
                    </div>
                </div>
<?php endif; ?>
            </div>
        </div>
        <p class="text-center small"><a href="<?=$_base.$_request?>">buscar otro documento</a></p>
<?php endif; ?>
    </div>
</div>
