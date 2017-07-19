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
namespace website\Dte\Admin;

/**
 * Clase para mapear la tabla dte_folio de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_folio
 * @author SowerPHP Code Generator
 * @version 2015-09-22 10:44:45
 */
class Model_DteFolio extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_folio'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $siguiente; ///< integer(32) NOT NULL DEFAULT ''
    public $disponibles; ///< integer(32) NOT NULL DEFAULT ''
    public $alerta; ///< integer(32) NOT NULL DEFAULT ''
    public $alertado; ///< boolean() NOT NULL DEFAULT 'false'

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'dte' => array(
            'name'      => 'Dte',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_tipo', 'column' => 'codigo')
        ),
        'certificacion' => array(
            'name'      => 'Certificacion',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'siguiente' => array(
            'name'      => 'Siguiente',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'disponibles' => array(
            'name'      => 'Disponibles',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'alerta' => array(
            'name'      => 'Alerta',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'alertado' => array(
            'name'      => 'Alertado',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte\Admin',
        'Model_DteTipo' => 'website\Dte\Admin'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método para guardar el mantenedor del folio usando una transacción
     * serializable
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function save($exitOnFailTransaction = true)
    {
        if (!$this->db->beginTransaction(true) and $exitOnFailTransaction)
            return false;
        parent::save();
        return $this->db->commit();
    }

    /**
     * Método que calcula la cantidad de folios que quedan disponibles y guarda
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-27
     */
    public function calcularDisponibles()
    {
        $this->db->beginTransaction(true);
        $cafs = $this->db->getTable('
            SELECT desde, hasta
            FROM dte_caf
            WHERE
                emisor = :emisor
                AND dte = :dte
                AND certificacion = :certificacion
                AND desde >= (
                    SELECT desde
                    FROM dte_caf
                    WHERE
                        emisor = :emisor
                        AND dte = :dte
                        AND certificacion = :certificacion
                        AND :folio BETWEEN desde AND hasta
                )
        ', [':emisor' => $this->emisor, ':dte'=>$this->dte, 'certificacion' => (int)$this->certificacion, ':folio'=>$this->siguiente]);
        $n_cafs = count($cafs);
        if (!$n_cafs)
            return false;
        if ($n_cafs==1) {
            $this->disponibles = $cafs[0]['hasta'] - $this->siguiente + 1;
        }
        else {
            for ($i=1; $i<$n_cafs; $i++) {
                if ($cafs[$i]['desde']!=($cafs[$i-1]['hasta']+1))
                    break;
            }
            $this->disponibles = $cafs[$i-1]['hasta'] - $this->siguiente + 1;
        }
        $status = $this->save(false);
        if (!$status) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return true;
    }

    /**
     * Método que entrega el listado de archivos CAF que existen cargados para
     * el tipo de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-19
     */
    public function getCafs()
    {
        $cafs = $this->db->getTable('
            SELECT desde, hasta, xml
            FROM dte_caf
            WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
            ORDER BY desde
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':certificacion'=>$this->certificacion]);
        foreach ($cafs as &$caf) {
            $xml = \website\Dte\Utility_Data::decrypt($caf['xml']);
            if (!$xml)
                return false;
            $Caf = new \sasco\LibreDTE\Sii\Folios($xml);
            $caf['fecha_autorizacion'] = $Caf->getFechaAutorizacion();
            $caf['meses_autorizacion'] = \sowerphp\general\Utility_Date::countMonths($caf['fecha_autorizacion']);
            unset($caf['xml']);
        }
        return $cafs;
    }

    /**
     * Método que entrega el objeto del tipo de DTE asociado al folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-19
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

}
