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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_compra de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla dte_compra
 * @author SowerPHP Code Generator
 * @version 2015-09-28 01:07:23
 */
class Model_DteCompras extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_compra'; ///< Tabla del modelo

    /**
     * Método que sincroniza el libro de compras local con el registro de compras del SII
     * - Se agregan documentos "registrados" en el registro de compras del SII
     * - Se eliminan documentos que están en el SII marcados como "no incluir" o "reclamados"
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-20
     */
    public function sincronizarRegistroComprasSII($meses = 2)
    {
        // periodos a procesar
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodos[] = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
        }
        sort($periodos);
        // sincronizar periodos
        foreach ($periodos as $periodo) {
            $config = ['periodo'=>$periodo];
            $this->agregarMasivo($this->getContribuyente()->getRCV(['operacion' => 'COMPRA', 'periodo' => $periodo, 'estado' => 'REGISTRO', 'tipo' => 'iecv']), $config);
            $this->eliminarMasivo($this->getContribuyente()->getRCV(['operacion' => 'COMPRA', 'periodo' => $periodo, 'estado' => 'NO_INCLUIR', 'tipo' => 'iecv']));
            $this->eliminarMasivo($this->getContribuyente()->getRCV(['operacion' => 'COMPRA', 'periodo' => $periodo, 'estado' => 'RECLAMADO', 'tipo' => 'iecv']));
        }
    }

    /**
     * Método que agrega masivamente documentos recibidos y acepta los intercambios asociados al DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-21
     */
    private function agregarMasivo($documentos, array $config = [])
    {
        $config = array_merge([
            'periodo' => (int)date('Ym'),
            'sucursal' => 0,
        ], $config);
        $DteIntercambios = (new Model_DteIntercambios())->setContribuyente($this->getContribuyente());
        foreach ($documentos as $doc) {
            // si el documento está anulado se omite
            if ($doc['anulado']) {
                continue;
            }
            // aceptar intercambios
            $intercambios = $DteIntercambios->buscarIntercambiosDte(substr($doc['rut'],0,-2), $doc['dte'], $doc['folio']);
            if ($intercambios) {
                foreach ($intercambios as $DteIntercambio) {
                    if (!$DteIntercambio->usuario and $DteIntercambio->documentos == 1) {
                        $DteIntercambio->responder(true, $config);
                    }
                }
            }
            // agregar el documento recibido si no existe (si no se encontró intercambio)
            $DteRecibido = new Model_DteRecibido(substr($doc['rut'],0,-2), $doc['dte'], $doc['folio'], (int)$this->getContribuyente()->config_ambiente_en_certificacion);
            if (!$DteRecibido->usuario) {
                $DteRecibido->tasa = $doc['tasa'];
                $DteRecibido->fecha = $doc['fecha'];
                $DteRecibido->sucursal_sii = $doc['sucursal_sii'];
                $DteRecibido->exento = $doc['exento'];
                $DteRecibido->neto = $doc['neto'];
                $DteRecibido->iva = $doc['iva'];
                $DteRecibido->total = $doc['total'];
                $DteRecibido->iva_uso_comun = $doc['iva_uso_comun'];
                $DteRecibido->iva_no_recuperable = $doc['iva_no_recuperable_monto'] ? json_encode([['codigo'=>$doc['iva_no_recuperable_codigo'], 'monto'=>$doc['iva_no_recuperable_monto']]]) : null;
                $DteRecibido->impuesto_adicional = null;
                $DteRecibido->impuesto_tipo = $doc['impuesto_tipo'];
                $DteRecibido->impuesto_sin_credito = $doc['impuesto_sin_credito'];
                $DteRecibido->monto_activo_fijo = $doc['monto_activo_fijo'];
                $DteRecibido->monto_iva_activo_fijo = $doc['monto_iva_activo_fijo'];
                $DteRecibido->iva_no_retenido = $doc['iva_no_retenido'];
                $DteRecibido->periodo = $config['periodo'];
                $DteRecibido->impuesto_puros = $doc['impuesto_puros'];
                $DteRecibido->impuesto_cigarrillos = $doc['impuesto_cigarrillos'];
                $DteRecibido->impuesto_tabaco_elaborado = $doc['impuesto_tabaco_elaborado'];
                $DteRecibido->impuesto_vehiculos = $doc['impuesto_vehiculos'];
                $DteRecibido->numero_interno = $doc['numero_interno'];
                $DteRecibido->emisor_nc_nd_fc = $doc['emisor_nc_nd_fc'];
                $DteRecibido->sucursal_sii_receptor = $config['sucursal'];
                $DteRecibido->rcv_accion = null;
                $DteRecibido->tipo_transaccion = null;
                $DteRecibido->receptor = $this->getContribuyente()->rut;
                $DteRecibido->usuario = $this->getContribuyente()->getUsuario()->id;
                $DteRecibido->save();
            }
        }
    }

    /**
     * Método que elimina masivamente documentos recibidos y los intercambios asociados al DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-20
     */
    private function eliminarMasivo($documentos)
    {
        $DteIntercambios = (new Model_DteIntercambios())->setContribuyente($this->getContribuyente());
        foreach ($documentos as $doc) {
            // eliminar DTE recibido
            $DteRecibido = new Model_DteRecibido();
            $DteRecibido->emisor = substr($doc['rut'],0,-2);
            $DteRecibido->dte = $doc['dte'];
            $DteRecibido->folio = $doc['folio'];
            $DteRecibido->certificacion = (int)$this->getContribuyente()->config_ambiente_en_certificacion;
            $DteRecibido->delete();
            // eliminar intercambio
            $intercambios = $DteIntercambios->buscarIntercambiosDte(substr($doc['rut'],0,-2), $doc['dte'], $doc['folio']);
            if ($intercambios) {
                foreach ($intercambios as $DteIntercambio) {
                    if ($DteIntercambio->documentos == 1) {
                        $DteIntercambio->delete();
                    }
                }
            }
        }
    }

}
