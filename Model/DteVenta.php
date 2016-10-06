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
 * Clase para mapear la tabla dte_venta de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_venta
 * @author SowerPHP Code Generator
 * @version 2015-09-25 21:47:15
 */
class Model_DteVenta extends Model_Base_Libro
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_venta'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $periodo; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $documentos; ///< integer(32) NOT NULL DEFAULT ''
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< integer(32) NULL DEFAULT ''
    public $revision_estado; ///< character varying(100) NULL DEFAULT ''
    public $revision_detalle; ///< character text() NULL DEFAULT ''

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
        'periodo' => array(
            'name'      => 'Periodo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
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
        'documentos' => array(
            'name'      => 'Documentos',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'xml' => array(
            'name'      => 'Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'track_id' => array(
            'name'      => 'Track Id',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_estado' => array(
            'name'      => 'Revision Estado',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_detalle' => array(
            'name'      => 'Revision Detalle',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte'
    ); ///< Namespaces que utiliza esta clase

    public static $libro_cols = [
        'dte' => 'TpoDoc',
        'folio' => 'NroDoc',
        'tasa' => 'TasaImp',
        'fecha' => 'FchDoc',
        'sucursal_sii' => 'CdgSIISucur',
        'rut' => 'RUTDoc',
        'razon_social' => 'RznSoc',
        'exento' => 'MntExe',
        'neto' => 'MntNeto',
        'iva' => 'MntIVA',
        'impuesto_codigo' => 'CodImp',
        'impuesto_tasa' => 'TasaImp',
        'impuesto_monto' => 'MntImp',
        'total' => 'MntTotal'
    ]; ///< Columnas del detalle del libro de ventas

    /**
     * Método que entrega el resumen real (de los detalles registrados) del
     * libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-06
     */
    public function getResumen()
    {
        $Libro = $this->getEmisor()->getLibroVentas($this->periodo);
        $resumen = $Libro->getResumen() + $this->getResumenManual();
        // limpiar resumen
        $campos = [
            'TpoDoc',
            'TotDoc',
            'TotAnulado',
            'TotOpExe',
            'TotMntExe',
            'TotMntNeto',
            'TotMntIVA',
            'TotIVAPropio',
            'TotIVATerceros',
            'TotLey18211',
            'TotMntTotal',
            'TotMntNoFact',
            'TotMntPeriodo',
        ];
        foreach ($resumen as &$r) {
            foreach ($r as $var => &$value) {
                if (!in_array($var, $campos)) {
                    unset($r[$var]);
                }
            }
        }
        return $resumen;
    }

     /**
     * Método que entrega el resumen manual, de los totales registrados al
     * enviar el libro al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-13
     */
    public function getResumenManual()
    {
        if ($this->_table=='dte_venta' and $this->xml) {
            $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            $Libro->loadXML(base64_decode($this->xml));
            return $Libro->getResumenManual();
        }
        return [];
    }

}
