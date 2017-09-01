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
 * @version 2016-08-07
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

}
