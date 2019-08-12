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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla registro_compra de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla registro_compra
 * @author SowerPHP Code Generator
 * @version 2019-08-09 13:54:15
 */
class Model_RegistroCompras extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'registro_compra'; ///< Tabla del modelo

    protected $estados = [
        'PENDIENTE', 'REGISTRO', 'NO_INCLUIR', 'RECLAMADO',
    ]; ///< Posibles estados y sus códigos (índices del arreglo)

    /**
     * Método que sincroniza los registros en estado PENDIENTE del registro de
     * compras del SII con un registro local para notificaciones en el sistema
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function sincronizar($estado = 'PENDIENTE', $meses = 2)
    {
        // periodos a procesar
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodo = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
            if ($periodo < 201708) {
                break;
            }
            $periodos[] = $periodo;
        }
        sort($periodos);
        // sincronizar periodos
        foreach ($periodos as $periodo) {
            $estado_codigo = $this->getEstadoCodigo($estado);
            $pendientes = $this->getContribuyente()->getRCV(['operacion' => 'COMPRA', 'periodo' => $periodo, 'estado' => $estado, 'tipo' => 'rcv']);
            $this->db->beginTransaction();
            $this->db->query(
                'DELETE FROM registro_compra WHERE receptor = :receptor AND periodo = :periodo AND certificacion = :certificacion AND estado = :estado',
                [':receptor'=>$this->getContribuyente()->rut, ':periodo'=>$periodo, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':estado'=>$estado_codigo]
            );
            foreach ($pendientes as $pendiente) {
                $RegistroCompra = new Model_RegistroCompra();
                $RegistroCompra->receptor = $this->getContribuyente()->rut;
                $RegistroCompra->periodo = $periodo;
                $RegistroCompra->estado = $estado_codigo;
                $RegistroCompra->certificacion = (int)$this->getContribuyente()->config_ambiente_en_certificacion;
                $RegistroCompra->set($this->normalizar($pendiente));
                $RegistroCompra->save();
            }
            $this->db->commit();
        }
    }

    /**
     * Método entrega el código de un estado a partir de su glosa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    private function getEstadoCodigo($estado)
    {
        $key = array_search($estado, $this->estados);
        return $key === false ? null : $key;
    }

    /**
     * Método que recibe un registro con el formato del SII (registro de compras)
     * y lo modifica para poder ser usado en el registro local de LibreDTE
     * (normalizándolo para el uso en la base de datos)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    private function normalizar($datos)
    {
        $registro = [];
        foreach ($datos as $key => $val) {
            if (in_array($key, ['detFchDoc', 'detFecAcuse', 'detFecReclamado', 'detFecRecepcion'])) {
                $aux = explode(' ', $val);
                if (!empty($aux[0])) {
                    list($d,$m,$Y) = explode('/', $aux[0]);
                    $val = $Y.'-'.$m.'-'.$d;
                }
                if (!empty($aux[1])) {
                    $val .= ' '.$aux[1];
                }
            }
            if (!$val and !in_array($key, ['detMntTotal', 'detTpoImp'])) {
                $val = null;
            }
            $registro[strtolower($key)] = $val;
        }
        return $registro;
    }

    /**
     * Método que entrega los documentos de compras pendientes de ser procesados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function buscar(array $filtros = [], $detalle = false)
    {
        $where = ['rc.receptor = :receptor', 'rc.certificacion = :certificacion', ];
        $vars = [':receptor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion];
        if (isset($filtros['estado'])) {
            $where[] = 'rc.estado = :estado';
            $vars[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['dte'])) {
            $where[] = 'rc.dettipodoc = :dte';
            $vars[':dte'] = $filtros['dte'];
        }
        if (!empty($filtros['total_desde'])) {
            $where[] = 'rc.detmnttotal >= :total_desde';
            $vars[':total_desde'] = $filtros['total_desde'];
        }
        if (!empty($filtros['total_hasta'])) {
            $where[] = 'rc.detmnttotal <= :total_hasta';
            $vars[':total_hasta'] = $filtros['total_hasta'];
        }
        if ($detalle) {
            $select = 'rc.*';
        } else {
            $select = '
                rc.estado,
                p.rut AS proveedor_rut,
                p.dv AS proveedor_dv,
                p.razon_social AS proveedor_razon_social,
                rc.dettipodoc AS dte,
                t.tipo AS dte_glosa,
                rc.detnrodoc AS folio,
                rc.detfchdoc AS fecha,
                rc.detfecrecepcion AS fecha_recepcion_sii,
                rc.detmntexe AS exento,
                rc.detmntneto AS neto,
                rc.detmntiva AS iva,
                rc.detmnttotal AS total,
                rc.dettipotransaccion
            ';
        }
        $pendientes = $this->db->getTable('
            SELECT
                '.$select.'
            FROM
                registro_compra AS rc
                JOIN contribuyente AS p ON p.rut = rc.detrutdoc
                JOIN dte_tipo AS t ON t.codigo = rc.dettipodoc
            WHERE
                '.implode(' AND ', $where).'
            ORDER BY
                detfecrecepcion
        ', $vars);
        $tipo_transacciones = \sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones;
        foreach ($pendientes as &$p) {
            $p['desctipotransaccion'] = !empty($tipo_transacciones[$p['dettipotransaccion']]) ? $tipo_transacciones[$p['dettipotransaccion']] : ('Tipo #'.$p['dettipotransaccion']);
        }
        return $pendientes;
    }

    /**
     * Método que entrega los documentos de compras pendientes de ser procesados
     * con su detalle completo del registro de compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function getDetalle(array $filtros = [])
    {
        return $this->buscar($filtros, true);
    }

    /**
     * Método que entrega las cantidad de documentos de compras pendientes de
     * ser procesados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-11
     */
    public function getResumenPendientes()
    {
        return $this->db->getTable('
            SELECT
                rc.dettipodoc AS dte,
                t.tipo AS dte_glosa,
                COUNT(rc.*) AS cantidad,
                MIN(rc.detfecrecepcion) AS fecha_recepcion_sii_inicial,
                MAX(rc.detfecrecepcion) AS fecha_recepcion_sii_final,
                SUM(rc.detmnttotal) AS total
            FROM
                registro_compra AS rc
                JOIN dte_tipo AS t ON t.codigo = rc.dettipodoc
            WHERE
                receptor = :receptor
                AND certificacion = :certificacion
                AND estado = 0
            GROUP BY rc.dettipodoc, t.tipo
            ORDER BY fecha_recepcion_sii_inicial
        ', [':receptor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion]);
    }

}
