<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/admin/itemes/listar?search=activo:1" title="Volver al mantenedor de items">
            <span class="fa fa-cubes"></span> Volver a items
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/admin/item_clasificaciones/exportar" title="Exportar clasificaciones desde archivo CSV">
            <span class="fa fa-download"></span> Exportar
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/admin/item_clasificaciones/importar" title="Importar clasificaciones desde archivo CSV">
            <span class="fa fa-upload"></span> Importar
        </a>
    </li>
</ul>

<h1>Listado de clasificaciones de Items</h1>
<p><?=$comment?></p>

<?php

// preparar títulos de columnas (con link para ordenar por dicho campo)
$titles = [];
$colsWidth = [];
foreach ($columns as $column => $info) {
    $titles[] = $info['name'].' '.
        '<div class="pull-right"><a href="'.$_base.$module_url.$controller.'/listar/'.$page.'/'.$column.'/A'.$searchUrl.'" title="Ordenar ascendentemente por '.$info['name'].'"><span class="fas fa-sort-alpha-down"></span></a>'.
        ' <a href="'.$_base.$module_url.$controller.'/listar/'.$page.'/'.$column.'/D'.$searchUrl.'" title="Ordenar descendentemente por '.$info['name'].'"><span class="fas fa-sort-alpha-up"></span></a></div>'
    ;
    $colsWidth[] = null;
}
$titles[] = 'Acciones';
$colsWidth[] = $actionsColsWidth;

// crear arreglo para la tabla y agregar títulos de columnas
$data = array($titles);

// agregar fila para búsqueda mediante formulario
$row = array();
$form = new \sowerphp\general\View_Helper_Form(false);
$optionsBoolean = array(array('', 'Seleccione una opción'), array('1', 'Si'), array('0', 'No'));
foreach ($columns as $column => &$info) {
    // si es un archivo
    if ($info['type']=='file') {
        $row[] = '';
    }
    // si es de tipo boolean se muestra lista desplegable
    else if ($info['type']=='boolean' || $info['type']=='tinyint') {
        $row[] = $form->input(array('type'=>'select', 'name'=>$column, 'options' => $optionsBoolean, 'value' => (isset($search[$column])?$search[$column]:'')));
    }
    // si es llave foránea
    else if ($info['fk']) {
        $class = 'Model_'.\sowerphp\core\Utility_Inflector::camelize(
            $info['fk']['table']
        );
        $classs = $fkNamespace[$class].'\Model_'.\sowerphp\core\Utility_Inflector::camelize(
            \sowerphp\core\Utility_Inflector::pluralize($info['fk']['table'])
        );
        $objs = new $classs();
        $options = $objs->getList();
        array_unshift($options, array('', 'Seleccione una opción'));
        $row[] = $form->input(array('type'=>'select', 'name'=>$column, 'options' => $options, 'value' => (isset($search[$column])?$search[$column]:'')));
    }
    // si es un tipo de dato de fecha o fecha con hora se muestra un input para fecha
    else if (in_array($info['type'], ['date', 'timestamp', 'timestamp without time zone'])) {
        $row[] = $form->input(array('type'=>'date', 'name'=>$column, 'value'=>(isset($search[$column])?$search[$column]:'')));
    }
    // si es cualquier otro tipo de datos
    else {
        $row[] = $form->input(array('name'=>$column, 'value'=>(isset($search[$column])?$search[$column]:'')));
    }
}
$row[] = '<button type="submit" class="btn btn-default"><span class="fa fa-search"></span></button>';
$data[] = $row;

// crear filas de la tabla
foreach ($Objs as &$obj) {
    $row = array();
    foreach ($columns as $column => &$info) {
        // si es un archivo
        if ($info['type']=='file') {
            if ($obj->{$column.'_size'})
                $row[] = '<a href="'.$_base.$module_url.$controller.'/d/'.$column.'/'.urlencode($obj->id).'"><span class="fa fa-download"></span></a>';
            else
                $row[] = '';
        }
        // si es boolean se usa Si o No según corresponda
        else if ($info['type']=='boolean' || $info['type']=='tinyint') {
            $row[] = $obj->{$column}=='t' || $obj->{$column}=='1' ? 'Si' : 'No';
        }
        // si es llave foránea
        else if ($info['fk']['table']) {
            // si no es vacía la columna
            if (!empty($obj->{$column})) {
                $method = 'get'.\sowerphp\core\Utility_Inflector::camelize($info['fk']['table']);
                $row[] = $obj->$method($obj->$column)->{$info['fk']['table']};
            } else {
                $row[] = '';
            }
        }
        // si es cualquier otro tipo de datos
        else {
            $row[] = $obj->{$column};
        }
    }
    $pkValues = $obj->getPkValues();
    $pkURL = implode('/', array_map('urlencode', $pkValues));
    $actions = '';
    if (!empty($extraActions)) {
        foreach ($extraActions as $a => $i) {
            $actions .= '<a href="'.$_base.$module_url.$controller.'/'.$a.'/'.$pkURL.$listarFilterUrl.'" title="'.(isset($i['desc'])?$i['desc']:'').'"><span class="'.$i['icon'].' btn btn-default"></span></a> ';
        }
    }
    $actions .= '<a href="'.$_base.$module_url.$controller.'/editar/'.$obj->codigo.$listarFilterUrl.'" title="Editar"><span class="fa fa-edit btn btn-default"></span></a>';
    if ($deleteRecord) {
        $actions .= ' <a href="'.$_base.$module_url.$controller.'/eliminar/'.$obj->codigo.$listarFilterUrl.'" title="Eliminar" onclick="return eliminar(\''.$model.'\', \''.implode(', ', $pkValues).'\')"><span class="fas fa-times btn btn-default"></span></a>';
    }
    $row[] = $actions;
    $data[] = $row;
}

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer ([
    'link' => $_base.$module_url.$controller,
    'linkEnd' => $linkEnd,
    'listarFilterUrl' => $listarFilterUrl
]);
$maintainer->setId($models);
$maintainer->setColsWidth($colsWidth);
echo $maintainer->listar ($data, $pages, $page);
