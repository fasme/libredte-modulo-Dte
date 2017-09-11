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
namespace website\Dte;

/**
 * Controlador de compras
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-09-07
 */
class Controller_DteCompras extends Controller_Base_Libros
{

    protected $config = [
        'model' => [
            'singular' => 'Compra',
            'plural' => 'Compras',
        ]
    ]; ///< Configuración para las acciones del controlador

    /**
     * Acción que permite importar un libro desde un archivo CSV
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-03-20
     */
    public function importar()
    {
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir el archivo con el libro
            if (!isset($_FILES['archivo']) or $_FILES['archivo']['error']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Ocurrió un error al subir el libro', 'error'
                );
                return;
            }
            // obtener receptor (contribuyente operando)
            $Receptor = $this->getContribuyente();
            $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            $Libro->agregarComprasCSV($_FILES['archivo']['tmp_name']);
            $detalle = $Libro->getCompras();
            // agregar cada documento del libro
            $keys = array_keys(Model_DteCompra::$libro_cols);
            $noGuardado = [];
            foreach ($detalle as $d) {
                $datos = array_combine($keys, $d);
                $emisor = explode('-', str_replace('.', '', $datos['rut']))[0];
                $DteRecibido = new Model_DteRecibido($emisor, $datos['dte'], $datos['folio'], $Receptor->config_ambiente_en_certificacion);
                $DteRecibido->set($datos);
                $DteRecibido->emisor = $emisor;
                $DteRecibido->receptor = $Receptor->rut;
                $DteRecibido->usuario = $this->Auth->User->id;
                if ($_POST['periodo'] and \sowerphp\general\Utility_Date::format($DteRecibido->fecha, 'Ym')!=$_POST['periodo']) {
                    $DteRecibido->periodo = (int)$_POST['periodo'];
                }
                // si el DTE es de producción y es electrónico entonces se consultará su
                // estado antes de poder guardar, esto evitará agregar documentos que no
                // han sido recibidos en el SII o sus datos son incorrectos
                $guardar = true;
                if (!$DteRecibido->certificacion and $DteRecibido->getTipo()->electronico and !$Receptor->config_recepcion_omitir_verificacion_sii) {
                    // obtener firma
                    $Firma = $Receptor->getFirma($this->Auth->User->id);
                    if (!$Firma) {
                        \sowerphp\core\Model_Datasource_Session::message(
                            'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
                        );
                        $this->redirect('/dte/admin/firma_electronicas');
                    }
                    // consultar estado dte
                    $estado = $DteRecibido->getEstado($Firma);
                    if ($estado===false) {
                        $guardar = false;
                        $noGuardado[] = 'T'.$DteRecibido->dte.'F'.$DteRecibido->folio.': '.implode(' / ', \sasco\LibreDTE\Log::readAll());
                    } else if (in_array($estado['ESTADO'], ['DNK', 'FAU', 'FNA', 'EMP'])) {
                        $guardar = false;
                        $noGuardado[] = 'T'.$DteRecibido->dte.'F'.$DteRecibido->folio.' Estado DTE: '.(is_array($estado)?implode('. ', $estado):$estado);
                    }
                }
                // guardar documento
                if ($guardar) {
                    try {
                        if (!$DteRecibido->save()) {
                            $noGuardado[] = 'T'.$DteRecibido->dte.'F'.$DteRecibido->folio;
                        }
                    } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                        $noGuardado[] = 'T'.$DteRecibido->dte.'F'.$DteRecibido->folio.': '.$e->getMessage();
                    }
                }
            }
            // mostrar errores o redireccionar
            if ($noGuardado) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Los siguientes documentos no se agregaron:<br/>- '.implode('<br/>- ', $noGuardado), 'error'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se importó el libro de compras', 'ok'
                );
                $this->redirect('/dte/dte_compras');
            }
        }
    }

    /**
     * Acción que envía el archivo XML del libro de compras al SII
     * Si no hay documentos en el período se enviará sin movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-01
     */
    public function enviar_sii($periodo)
    {
        $Emisor = $this->getContribuyente();
        // si el libro fue enviado y no es rectifica error
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if ($DteCompra->track_id and empty($_POST['CodAutRec']) and $DteCompra->getEstado()!='LRH' and $DteCompra->track_id!=-1) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro del período '.$periodo.' ya fue enviado, ahora sólo puede  hacer rectificaciones', 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // si el periodo es mayor o igual al actual no se puede enviar
        if ($periodo >= date('Ym')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede enviar el libro de compras del período '.$periodo.', debe esperar al mes siguiente del período', 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // agregar carátula al libro
        $Libro = $Emisor->getLibroCompras($periodo);
        $caratula = [
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'RutEnvia' => $Firma->getID(),
            'PeriodoTributario' => substr($periodo, 0, 4).'-'.substr($periodo, 4),
            'FchResol' => $Emisor->config_ambiente_en_certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' =>  $Emisor->config_ambiente_en_certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
            'TipoOperacion' => 'COMPRA',
            'TipoLibro' => 'MENSUAL',
            'TipoEnvio' => 'TOTAL',
        ];
        if (!empty($_POST['CodAutRec'])) {
            $caratula['TipoLibro'] = 'RECTIFICA';
            $caratula['CodAutRec'] = $_POST['CodAutRec'];
        }
        $Libro->setCaratula($caratula);
        // obtener XML
        $Libro->setFirma($Firma);
        $xml = $Libro->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el libro de compras<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // enviar al SII sólo si el libro es de un período menor o igual al 201707
        // esto ya que desde 201708 se reemplaza por RCV
        if ($periodo <= 201707) {
            $track_id = $Libro->enviar();
            $revision_estado = null;
            $revision_detalle = null;
            if (!$track_id) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible enviar el libro de compras al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
                $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
            }
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro de compras período '.$periodo.' envíado', 'ok'
            );
        } else {
            $track_id = -1;
            $revision_estado = 'Libro generado';
            $revision_detalle = 'No se envió al SII, ya que se reemplazó por RCV';
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro de compras período '.$periodo.' generado, pero no se envió al SII, ya que se reemplazó por RCV', 'ok'
            );
        }
        // guardar libro de compras
        $DteCompra->documentos = $Libro->cantidad();
        $DteCompra->xml = base64_encode($xml);
        $DteCompra->track_id = $track_id;
        $DteCompra->revision_estado = $revision_estado;
        $DteCompra->revision_detalle = $revision_detalle;
        $DteCompra->save();
        $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
    }

    /**
     * Acción que genera la imagen del gráfico de torta de compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function grafico_tipos($periodo)
    {
        $Emisor = $this->getContribuyente();
        $compras = $Emisor->getComprasPorTipo($periodo);
        $chart = new \sowerphp\general\View_Helper_Chart();
        $chart->pie('Compras por tipo de DTE del período '.$periodo, $compras);
    }

     /**
     * Acción que genera el archivo CSV con el registro de compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-06
     */
    public function descargar_registro_compra($periodo, $electronico = null)
    {
        $Emisor = $this->getContribuyente();
        $compras = $Emisor->getCompras($periodo, is_numeric($electronico) ? $electronico : null);
        if (!$compras) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay documentos de compra del período '.$periodo, 'warning'
            );
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        foreach ($compras as &$c) {
            unset($c['anulado'], $c['impuesto_vehiculos'], $c['iva_uso_comun_factor']);
        }
        $columnas = Model_DteCompra::$libro_cols;
        unset($columnas['anulado'], $columnas['impuesto_vehiculos'], $columnas['iva_uso_comun_factor']);
        $columnas['tipo_transaccion'] = 'Tipo Transaccion';
        array_unshift($compras, $columnas);
        \sowerphp\general\Utility_Spreadsheet_CSV::generate($compras, 'rc_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo, ';', '');
    }

    /**
     * Acción que genera el archivo CSV con los resúmenes de ventas (ingresados manualmente)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-06
     */
    public function descargar_tipo_transacciones($periodo)
    {
        $Emisor = $this->getContribuyente();
        $compras = $Emisor->getCompras($periodo, [33, 34, 43, 46, 56, 61]);
        $datos = [];
        foreach ($compras as $c) {
            if (!$c['tipo_transaccion']) {
                continue;
            }
            $codigo_impuesto = null;
            if ($c['iva_uso_comun']) {
                if (empty($c['tipo_transaccion'])) {
                    $c['tipo_transaccion'] = 2;
                } else {
                    $codigo_impuesto = 2;
                }
            }
            if ($c['iva_no_recuperable_codigo']) {
                $c['tipo_transaccion'] = 6;
                $codigo_impuesto = $c['iva_no_recuperable_codigo'];
            }
            $datos[] = [
                $c['rut'],
                $c['dte'],
                $c['folio'],
                $c['tipo_transaccion'],
                $codigo_impuesto,
            ];
        }
        if (!$datos) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay compras caracterizadas para el período '.$periodo, 'warning'
            );
            $this->redirect(str_replace('descargar_tipo_transacciones', 'ver', $this->request->request));
        }
        // generar CSV
        array_unshift($datos, ['Rut-DV', 'Codigo_Tipo_Doc', 'Folio_Doc', 'TpoTranCompra', 'Codigo_IVA_E_Imptos']);
        \sowerphp\general\Utility_Spreadsheet_CSV::generate($datos, 'rc_tipo_transacciones_'.$periodo, ';', '');
    }

    /**
     * Acción que permite obtener el resumen del registro de compra para un período y estado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-07
     */
    public function rcv_resumen($periodo, $estado = 'REGISTRO')
    {
        $Emisor = $this->getContribuyente();
        try {
            $resumen = $Emisor->getRCV(['operacion' => 'COMPRA', 'periodo' => $periodo, 'estado' => $estado, 'detalle'=>false]);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
            'estado' => $estado,
            'resumen' => $resumen,
        ]);
    }

    /**
     * Acción que permite obtener el detalle del registro de compra para un período y estado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-10
     */
    public function rcv_detalle($periodo, $dte, $estado = 'REGISTRO')
    {
        $Emisor = $this->getContribuyente();
        try {
            $detalle = $Emisor->getRCV(['operacion' => 'COMPRA', 'periodo' => $periodo, 'dte' => $dte, 'estado' => $estado]);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        if (!$detalle) {
            \sowerphp\core\Model_Datasource_Session::message('No hay detalle para el período y estado solicitados', 'warning');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
            'DteTipo' => new \website\Dte\Admin\Mantenedores\Model_DteTipo($dte),
            'estado' => $estado,
            'detalle' => $detalle,
        ]);
    }

    /**
     * Acción que permite obtener las diferencias entre el registro de compras y lo que está en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-10
     */
    public function rcv_diferencias($periodo, $dte)
    {
        $Emisor = $this->getContribuyente();
        $documentos_libredte_todos = $Emisor->getCompras($periodo, [$dte]);
        // obtener documentos en el registro de compra del SII con estado REGISTRO
        try {
            $documentos_rc_todos = $Emisor->getRCV(['operacion' => 'COMPRA', 'periodo' => $periodo, 'dte' => $dte, 'estado' => 'REGISTRO']);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        if (!$documentos_rc_todos) {
            \sowerphp\core\Model_Datasource_Session::message('No hay detalle para el período y estado solicitados', 'warning');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        // crear documentos rc
        $documentos_rc = [];
        foreach ($documentos_rc_todos as $dte_rc) {
            $existe = false;
            foreach ($documentos_libredte_todos as $dte_libredte) {
                if ($dte_rc['detRutDoc']==explode('-', $dte_libredte['rut'])[0] and $dte_rc['detNroDoc']==$dte_libredte['folio']) {
                    $existe = true;
                    break;
                }
            }
            if (!$existe) {
                $documentos_rc[] = $dte_rc;
            }
        }
        // crear documentos libredte
        $documentos_libredte = [];
        foreach ($documentos_libredte_todos as $dte_libredte) {
            $existe = false;
            foreach ($documentos_rc_todos as $dte_rc) {
                if ($dte_rc['detRutDoc']==explode('-', $dte_libredte['rut'])[0] and $dte_rc['detNroDoc']==$dte_libredte['folio']) {
                    $existe = true;
                    break;
                }
            }
            if (!$existe) {
                $documentos_libredte[] = $dte_libredte;
            }
        }
        // asignar a la vista
        $this->set([
            'periodo' => $periodo,
            'dte' => $dte,
            'documentos_rc' => $documentos_rc,
            'documentos_libredte' => $documentos_libredte,
        ]);
    }

}
