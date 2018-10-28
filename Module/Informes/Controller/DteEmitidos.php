<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
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
namespace website\Dte\Informes;

/**
 * Clase para informes de los documentos emitidos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-09-24
 */
class Controller_DteEmitidos extends \Controller_App
{

    /**
     * Acción principal del informe de ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-24
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $desde = isset($_POST['desde']) ? $_POST['desde'] : date('Y-m-01');
        $hasta = isset($_POST['hasta']) ? $_POST['hasta'] : date('Y-m-d');
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
        if (isset($_POST['submit'])) {
            $DteEmitidos = (new \website\Dte\Model_DteEmitidos())->setContribuyente($Emisor);
            $this->set([
                'por_tipo' => $DteEmitidos->getPorTipo($desde, $hasta),
                'por_dia' => $DteEmitidos->getPorDia($desde, $hasta),
                'por_hora' => $DteEmitidos->getPorHora($desde, $hasta),
                'por_sucursal' => $DteEmitidos->getPorSucursal($desde, $hasta),
                'por_usuario' => $DteEmitidos->getPorUsuario($desde, $hasta),
                'por_nacionalidad' => $DteEmitidos->getPorNacionalidad($desde, $hasta),
                'por_moneda' => $DteEmitidos->getPorMoneda($desde, $hasta),
            ]);
        }
    }

    /**
     * Acción que entrega el informe de ventas en CSV
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-10-27
     */
    public function csv($desde, $hasta)
    {
        extract($this->Api->getQuery([
            'detalle' => false,
        ]));
        $Emisor = $this->getContribuyente();
        $cols = [
            'ID',
            'Documento',
            'Folio',
            'Fecha',
            'RUT',
            'Razón social',
            'Exento',
            'Neto',
            'IVA',
            'Total CLP',
            'Nacionalidad',
            'Moneda',
            'Total moneda',
            'Sucursal',
            'Usuario',
            'Fecha y hora timbre',
            'Intercambio',
            'Evento receptor',
            'Cedido',
        ];
        if ($detalle) {
            $cols[] = 'Código';
            $cols[] = 'Item';
            $cols[] = 'Cantidad';
            $cols[] = 'Unidad';
            $cols[] = 'Exento';
            $cols[] = 'Neto';
            $cols[] = 'Descuento %';
            $cols[] = 'Descuento $';
            $cols[] = 'Subtotal';
        }
        $aux = (new \website\Dte\Model_DteEmitidos())->setContribuyente($Emisor)->getDetalle($desde, $hasta, $detalle);
        if ($aux and $detalle) {
            $emitidos = [];
            foreach($aux as $e) {
                $i = 1;
                foreach ($e['items'] as $item) {
                    if ($i==1) {
                        $emitido = array_slice($e, 0, 19);
                    } else {
                        $emitido = array_fill(0, 19, '');
                    }
                    foreach  ($item as $key => $val) {
                        $emitido[$key] = $val;
                    }
                    $emitidos[] = $emitido;
                    $i++;
                }
            }
            unset($aux);
        } else {
            $emitidos = $aux;
        }
        array_unshift($emitidos, $cols);
        \sowerphp\general\Utility_Spreadsheet_CSV::generate($emitidos, 'emitidos_'.$Emisor->rut.'_'.$desde.'_'.$hasta);
    }

    /**
     * Acción que permite buscar los estados de los dte emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-23
     */
    public function estados($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/estados/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde and $hasta) ? $Emisor->getDocumentosEmitidosResumenEstados($desde, $hasta) : false,
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos con cierto estado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-23
     */
    public function estados_detalle($desde, $hasta, $estado = null)
    {
        $Emisor = $this->getContribuyente();
        $estado = urldecode($estado);
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
            'estado' => $estado,
            'documentos' => $Emisor->getDocumentosEmitidosEstado($desde, $hasta, $estado),
        ]);
    }

    /**
     * Acción que permite buscar los eventos de los dte emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-23
     */
    public function eventos($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/eventos/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde and $hasta) ? $Emisor->getDocumentosEmitidosResumenEventos($desde, $hasta) : false,
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos con cierto evento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-04-25
     */
    public function eventos_detalle($desde, $hasta, $evento = null)
    {
        $Emisor = $this->getContribuyente();
        $evento = urldecode($evento);
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
            'evento' => $evento,
            'documentos' => $Emisor->getDocumentosEmitidosEvento($desde, $hasta, $evento),
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos pero que aun no se
     * envian al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-23
     */
    public function sin_enviar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'documentos' => $Emisor->getDocumentosEmitidosSinEnviar(),
        ]);
    }

    /**
     * Acción que permite buscar los DTE emitidos sin envíos de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-01-26
     */
    public function sin_intercambio($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/sin_intercambio/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde and $hasta) ? (new \website\Dte\Model_DteEmitidos())->setContribuyente($Emisor)->getSinEnvioIntercambio($desde, $hasta) : false,
        ]);
    }

    /**
     * Acción que permite buscar las respuestas de los procesos de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-23
     */
    public function intercambio($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/intercambio/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde and $hasta) ? $Emisor->getDocumentosEmitidosResumenEstadoIntercambio($desde, $hasta) : false,
        ]);
    }

    /**
     * Acción que permite buscar los detalles de los intercambios por ciertas respuestas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function intercambio_detalle($desde, $hasta, $recibo = null, $recepcion = null, $resultado = null)
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
            'recibo' => $recibo ? 'si' : 'no',
            'recepcion' => $recepcion!==null ? (isset(\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$recepcion]) ? \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$recepcion] : $recepcion) : null,
            'resultado' => $resultado!==null ? (isset(\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$resultado]) ? \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$resultado] : $resultado) : null,
            'documentos' => $Emisor->getDocumentosEmitidosEstadoIntercambio($desde, $hasta, $recibo, $recepcion, $resultado),
        ]);
    }

}
