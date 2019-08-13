<?php

/**
 * SowerPHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// namespace del controlador
namespace website\Dte;

/**
 * Clase para el controlador asociado a la tabla registro_compra de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla registro_compra
 * @author SowerPHP Code Generator
 * @version 2019-08-09 13:54:15
 */
class Controller_RegistroCompras extends \Controller_App
{

    /**
     * Acción principal que redirecciona a los documentos pendientes, ya que no
     * se deberían estar cargando de otro tipo actualmente, quizás en el futuro (?)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-13
     */
    public function index()
    {
        $this->redirect('/dte/registro_compras/pendientes');
    }

    /**
     * Acción para mostrar los documentos recibidos en SII con estado pendientes
     * de procesar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function pendientes()
    {
        $filtros = array_merge($this->getQuery([
            'emisor' => null,
            'fecha_desde' => null,
            'fecha_hasta' => null,
            'dte' => null,
            'total_desde' => null,
            'total_hasta' => null,
        ]), ['estado' => 0]); // forzar estado PENDIENTE
        $Receptor = $this->getContribuyente();
        $documentos = (new Model_RegistroCompras())->setContribuyente($Receptor)->buscar($filtros);
        $this->set([
            'filtros' => $filtros,
            'documentos' => $documentos,
        ]);
    }

    /**
     * Acción para generar un CSV con los documentos recibidos en SII con
     * estado pendientes de procesar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function csv()
    {
        $filtros = array_merge($this->getQuery([
            'emisor' => null,
            'fecha_desde' => null,
            'fecha_hasta' => null,
            'dte' => null,
            'total_desde' => null,
            'total_hasta' => null,
        ]), ['estado' => 0]); // forzar estado PENDIENTE
        $Receptor = $this->getContribuyente();
        $documentos = (new Model_RegistroCompras())->setContribuyente($Receptor)->getDetalle($filtros);
        if (!$documentos) {
            \sowerphp\core\Model_Datasource_Session::message('No hay documentos recibidos en SII para la búsqueda realizada');
            $this->redirect('/dte/registro_compras');
        }
        array_unshift($documentos, array_keys($documentos[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($documentos);
        $this->response->sendContent($csv, $Receptor->rut.'-'.$Receptor->dv.'_recibidos_'.date('YmdHis').'.csv');
    }

    /**
     * Acción para el buscador de documentos recibidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-13
     */
    public function buscar()
    {
        $Receptor = $this->getContribuyente();
        $this->set([
            'Receptor' => $Receptor,
            'dte_tipos' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(),
        ]);
        if (isset($_POST['submit'])) {
            unset($_POST['submit']);
            $filtros = array_merge($_POST, ['estado' => 0]); // forzar estado PENDIENTE
            // obtener PDF desde servicio web
            $r = $this->consume('/api/dte/registro_compras/buscar/'.$Receptor->rut, $filtros);
            if ($r['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($r['body'], 'error');
                return;
            }
            if (empty($r['body'])) {
                \sowerphp\core\Model_Datasource_Session::message(__('No hay documentos recibidos en SII para la búsqueda realizada'), 'warning');
            }
            $this->set([
                'filtros' => $filtros,
                'documentos' => $r['body'],
            ]);
        }
    }

    /**
     * API que permite buscar en los documentos recibidos en el registro de
     * compras del SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-13
     */
    public function _api_buscar_POST($receptor)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear receptor
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            $this->Api->send(__('Receptor no existe'), 404);
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/registro_compras/buscar')) {
            $this->Api->send(__('No está autorizado a operar con la empresa solicitada'), 403);
        }
        // obtener boletas
        $filtros = [];
        foreach ($this->Api->data as $key => $val) {
            if (!empty($val)) {
                $filtros[$key] = $val;
            }
        }
        if (empty($filtros)) {
            $this->Api->send(__('Debe definir a lo menos un filtro para la búsqueda'), 400);
        }
        $filtros['estado'] = 0; // forzar estado PENDIENTE
        $documentos = (new Model_RegistroCompras())->setContribuyente($Receptor)->buscar($filtros);
        $this->Api->send($documentos, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción para actualizar el listado de documentos del registro de compras
     * del SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-13
     */
    public function actualizar()
    {
        $estado = 'PENDIENTE'; // forzar estado PENDIENTE
        $meses = 2;
        $Receptor = $this->getContribuyente();
        try {
            (new Model_RegistroCompras())->setContribuyente($Receptor)->sincronizar($estado, $meses);
            \sowerphp\core\Model_Datasource_Session::message(__('Documentos recibidos con estado %s actualizados', $estado), 'ok');
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
        }
        $this->redirect('/dte/registro_compras');
    }

}
