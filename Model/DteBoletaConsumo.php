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
 * Clase para mapear la tabla dte_boleta_consumo de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_boleta_consumo
 * @author SowerPHP Code Generator
 * @version 2016-02-14 05:05:56
 */
class Model_DteBoletaConsumo extends Model_Base_Envio
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_boleta_consumo'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dia; ///< date() NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $secuencia; ///< integer(32) NOT NULL DEFAULT ''
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< integer(32) NULL DEFAULT ''
    public $revision_estado; ///< character varying(100) NULL DEFAULT ''
    public $revision_detalle; ///< text() NULL DEFAULT ''

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
        'dia' => array(
            'name'      => 'Día',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
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
        'secuencia' => array(
            'name'      => 'Secuencia',
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
            'name'      => 'Track ID',
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
            'name'      => 'Estado revisión',
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
            'name'      => 'Recepción SII',
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

    /**
     * Método que envia el reporte de consumo de folios al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function enviar()
    {
        $ConsumoFolio = $this->generarConsumoFolio();
        $xml = $ConsumoFolio->generar();
        if (!$ConsumoFolio->schemaValidate())
            return false;
        $this->track_id = $ConsumoFolio->enviar();
        if (!$this->track_id)
            return false;
        $this->secuencia = $ConsumoFolio->getSecuencia();
        $this->xml = base64_encode($xml);
        $this->revision_estado = null;
        $this->revision_detalle = null;
        return $this->save() ? $this->track_id : false;
    }

    /**
     * Método que entrega el XML del consumo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function getXML()
    {
        if ($this->xml) {
            return base64_decode($this->xml);
        }
        return $this->generarXML();
    }

    /**
     * Método que genera el XML del consumo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    private function generarXML()
    {
        $ConsumoFolio = $this->generarConsumoFolio();
        $xml = $ConsumoFolio->generar();
        if (!$ConsumoFolio->schemaValidate())
            return false;
        return $xml;
    }

    /**
     * Método que crea el objeto del consumo de folios de LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    private function generarConsumoFolio()
    {
        $Emisor = $this->getEmisor();
        $dtes = [];
        foreach ($Emisor->getDocumentosAutorizados() as $dte) {
            if (in_array($dte['codigo'], [39, 41, 61])) {
                $dtes[] = $dte['codigo'];
            }
        }
        sort($dtes);
        $documentos = $Emisor->getDocumentosConsumoFolios($this->dia);
        $ConsumoFolio = new \sasco\LibreDTE\Sii\ConsumoFolio();
        $ConsumoFolio->setFirma($Emisor->getFirma());
        $ConsumoFolio->setDocumentos($dtes);
        foreach ($documentos as $documento) {
            $ConsumoFolio->agregar([
                'TpoDoc' => $documento['dte'],
                'NroDoc' => $documento['folio'],
                'TasaImp' => $documento['tasa'],
                'FchDoc' => $documento['fecha'],
                'MntExe' => $documento['exento'],
                'MntNeto' => $documento['neto'],
                'MntIVA' => $documento['iva'],
                'MntTotal' => $documento['total'],
            ]);
        }
        $ConsumoFolio->setCaratula([
            'RutEmisor' => $Emisor->rut.'-'.$Emisor->dv,
            'FchResol' => $Emisor->config_ambiente_en_certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' =>  $Emisor->config_ambiente_en_certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
            'FchInicio' => $this->dia,
            'FchFinal' => $this->dia,
            'SecEnvio' => $this->secuencia + 1,
        ]);
        return $ConsumoFolio;
    }

}
