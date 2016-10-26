<h1>Dte &raquo; Informes &raquo; Documentos emitidos</h1>
<p>Aquí podrá generar el informe de documentos emitidos de la empresa <?=$Emisor->razon_social?> para un rango determinado de tiempo.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => $desde,
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => $hasta,
    'check' => 'notempty date',
]);
echo $f->end('Generar informe de documentos emitidos');
?>
<?php if (isset($_POST['submit'])) : ?>
<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> Emitidos por día
            </div>
            <div class="panel-body">
                <div id="grafico-por_dia"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> Emitidos por hora
            </div>
            <div class="panel-body">
                <div id="grafico-por_hora"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> Emitidos por sucursal
            </div>
            <div class="panel-body">
                <div id="grafico-por_sucursal"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> Emitidos por usuario
            </div>
            <div class="panel-body">
                <div id="grafico-por_usuario"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> Emitidos por tipo de documento
            </div>
            <div class="panel-body">
                <div id="grafico-por_tipo"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> Emitidos por nacionalidad (sólo exportación)
            </div>
            <div class="panel-body">
                <div id="grafico-por_nacionalidad"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> Emitidos por moneda (sólo exportación)
            </div>
            <div class="panel-body">
                <div id="grafico-por_moneda"></div>
            </div>
        </div>
    </div>
</div>
<a class="btn btn-primary btn-lg btn-block" href="dte_emitidos/csv/<?=$desde?>/<?=$hasta?>" role="button">Descargar detalle de documentos emitidos en CSV</a>
<script>
Morris.Line({
    element: 'grafico-por_dia',
    data: <?=json_encode($por_dia)?>,
    xkey: 'dia',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
Morris.Line({
    element: 'grafico-por_hora',
    data: <?=json_encode($por_hora)?>,
    xkey: 'hora',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_sucursal',
    data: <?=json_encode($por_sucursal)?>,
    xkey: 'sucursal',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_usuario',
    data: <?=json_encode($por_usuario)?>,
    xkey: 'usuario',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_nacionalidad',
    data: <?=json_encode($por_nacionalidad)?>,
    xkey: 'nacionalidad',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_moneda',
    data: <?=json_encode($por_moneda)?>,
    xkey: 'moneda',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_tipo',
    data: <?=json_encode($por_tipo)?>,
    xkey: 'tipo',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
</script>
<?php endif; ?>
