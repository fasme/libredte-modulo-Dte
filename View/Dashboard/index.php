<div class="page-header"><h1>Facturación electrónica <small>dashboard <?=$Emisor->getNombre()?></small></h1></div>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-red">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-file-o fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?=num($n_temporales)?></div>
                        <div>Documentos temporales</div>
                    </div>
                </div>
            </div>
            <a href="dte_tmps">
                <div class="panel-footer">
                    <span class="pull-left">Ver documentos</span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-green">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-sign-out fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?=num($n_emitidos)?></div>
                        <div>Documentos emitidos</div>
                    </div>
                </div>
            </div>
            <a href="dte_ventas/ver/<?=$periodo?>">
                <div class="panel-footer">
                    <span class="pull-left">Ir al libro de ventas</span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-yellow">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-sign-in fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?=num($n_recibidos)?></div>
                        <div>Documentoss recibidos</div>
                    </div>
                </div>
            </div>
            <a href="dte_compras/ver/<?=$periodo?>">
                <div class="panel-footer">
                    <span class="pull-left">Ir al libro de compras</span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-exchange fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?=num($n_intercambios)?></div>
                        <div>Intercambios pendientes</div>
                    </div>
                </div>
            </div>
            <a href="dte_intercambios/listar">
                <div class="panel-footer">
                    <span class="pull-left">Ir a la bandeja</span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- PANEL IZQUIERDA -->
    <div class="col-md-3">
        <a class="btn btn-primary btn-lg btn-block" href="documentos/emitir" role="button">
            Emitir documento
        </a>
        <br />
        <!-- menú módulo -->
        <div class="list-group">
<?php foreach ($nav as $link=>&$info): ?>
            <a href="<?=$_base.'/dte'.$link?>" title="<?=$info['desc']?>" class="list-group-item">
                <i class="<?=$info['icon']?> fa-fw"></i> <?=$info['name']?>
            </a>
<?php endforeach; ?>
        </div>
        <!-- fin menú módulo -->
    </div>
    <!-- FIN PANEL IZQUIERDA -->
    <!-- PANEL CENTRO -->
    <div class="col-md-6">
        <!-- alertas envío libro o propuesta f29 -->
        <div class="row">
            <div class="col-xs-12">
<?php if (!$libro_ventas) : ?>
                <a class="btn btn-danger btn-lg btn-block" href="dte_ventas" role="button" title="Ir al libro de ventas">
                    <i class="fa fa-exclamation-circle"></i>
                    Está pendiente el envio del libro de ventas <?=$periodo_anterior?>
                </a>
                <br />
<?php endif; ?>
<?php if (!$libro_compras) : ?>
                <a class="btn btn-danger btn-lg btn-block" href="dte_compras" role="button" title="Ir al libro de compras">
                    <i class="fa fa-exclamation-circle"></i>
                    Está pendiente el envio del libro de compras <?=$periodo_anterior?>
                </a>
                <br />
<?php endif; ?>
<?php if ($propuesta_f29) : ?>
                <a class="btn btn-info btn-lg btn-block" href="informes/impuestos/propuesta_f29/<?=$periodo_anterior?>" role="button" title="Descargar archivo con la propuesta del formulario 29">
                    <i class="fa fa-download"></i>
                    Descargar propuesta F29 <?=$periodo_anterior?>
                </a>
                <br />
<?php endif; ?>
            </div>
        </div>
        <!-- fin alertas envío libro o propuesta f29 -->
        <!-- graficos ventas y compras -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart-o fa-fw"></i> Ventas período <?=$periodo?>
                    </div>
                    <div class="panel-body">
                        <div id="grafico-ventas"></div>
                        <a href="dte_ventas/ver/<?=$periodo?>" class="btn btn-default btn-block">Ver libro del período</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart-o fa-fw"></i> Compras período <?=$periodo?>
                    </div>
                    <div class="panel-body">
                        <div id="grafico-compras"></div>
                        <a href="dte_compras/ver/<?=$periodo?>" class="btn btn-default btn-block">Ver libro del período</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin graficos ventas y compras -->
        <!-- estado de documentos emitidos -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart-o fa-fw"></i> Estado documentos emitidos período <?=$periodo?>
                    </div>
                    <div class="panel-body">
                        <div id="grafico-dte_emitidos_estados"></div>
                        <a href="informes/dte_emitidos/estados/<?=$desde?>/<?=$hasta?>" class="btn btn-default btn-block">Ver detalles</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin estado de documentos emitidos -->
    </div>
    <!-- FIN PANEL CENTRO -->
    <!-- PANEL DERECHA -->
    <div class="col-md-3">
<?php if ($cuota) : ?>
        <!-- dtes usados (totales de emitidos y recibidos) -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-calculator fa-fw"></i>
                Documentos usados
            </div>
            <div class="panel-body text-center">
                <span class="lead text-info"><?=num($n_dtes)?></span> <small class="text-muted"> de <?=num($cuota)?></small><br/>
                <span style="font-size:0.8em"><a href="<?=$_base?>/dte/informes/documentos_usados">ver detalle de uso</a></span>
            </div>
        </div>
        <!-- fin dtes usados (totales de emitidos y recibidos) -->
<?php endif; ?>
        <!-- folios disponibles -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-file-code-o fa-fw"></i>
                Folios disponibles
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu slidedown">
                        <li>
                            <a href="admin/dte_folios/subir_caf">
                                <i class="fa fa-upload fa-fw"></i> Subir CAF
                            </a>
                        </li>
                        <li>
                            <a href="admin/dte_folios/agregar">
                                <i class="fa fa-edit fa-fw"></i> Crear folio
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="admin/dte_folios">
                                <i class="fa fa-cogs fa-fw"></i> Mantenedor
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-body">
<?php foreach ($folios as $label => $value) : ?>
                <p><?=$label?></p>
                <div class="progress">
                    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?=$value?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=$value?>%;">
                        <?=$value?>%
                    </div>
                </div>
<?php endforeach; ?>
            </div>
        </div>
        <!-- fin folios disponibles -->
        <!-- firma electrónica -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-certificate fa-fw"></i>
                Firma electrónica
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu slidedown">
                        <li>
                            <a href="admin/firma_electronicas/descargar">
                                <i class="fa fa-download fa-fw"></i> Descargar
                            </a>
                        </li>
                        <li>
                            <a href="admin/firma_electronicas/agregar">
                                <i class="fa fa-edit fa-fw"></i> Agregar
                            </a>
                        </li>
                        <li>
                            <a href="admin/firma_electronicas/eliminar">
                                <i class="fa fa-remove fa-fw"></i> Eliminar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-body">
<?php if ($Firma) : ?>
                <p><?=$Firma->getName()?></p>
                <span class="pull-right text-muted small"><em><?=$Firma->getID()?></em></span>
<?php else: ?>
                <p>No hay firma asociada al usuario ni a la empresa</p>
<?php endif; ?>
            </div>
        </div>
        <!-- firma electrónica -->
        <a class="btn btn-success btn-lg btn-block" href="admin/respaldos/exportar/all" role="button">
            <span class="fa fa-download"> Generar respaldo
        </a>
    </div>
    <!-- FIN PANEL DERECHA -->
</div>

<script>
Morris.Donut({
    element: 'grafico-ventas',
    data: <?=json_encode($ventas_periodo)?>,
    resize: true
});
Morris.Donut({
    element: 'grafico-compras',
    data: <?=json_encode($compras_periodo)?>,
    resize: true
});
Morris.Bar({
    element: 'grafico-dte_emitidos_estados',
    data: <?=json_encode($emitidos_estados)?>,
    xkey: 'estado',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
</script>
