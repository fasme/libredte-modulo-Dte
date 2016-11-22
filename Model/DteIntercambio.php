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
 * Clase para mapear la tabla dte_intercambio de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_intercambio
 * @author SowerPHP Code Generator
 * @version 2015-10-08 18:03:39
 */
class Model_DteIntercambio extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_intercambio'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $codigo; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $fecha_hora_email; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $asunto; ///< character varying(100) NOT NULL DEFAULT ''
    public $de; ///< character varying(80) NOT NULL DEFAULT ''
    public $responder_a; ///< character varying(80) NULL DEFAULT ''
    public $mensaje; ///< text() NULL DEFAULT ''
    public $mensaje_html; ///< text() NULL DEFAULT ''
    public $emisor; ///< integer(32) NOT NULL DEFAULT ''
    public $fecha_hora_firma; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $documentos; ///< smallint(16) NOT NULL DEFAULT ''
    public $archivo; ///< character varying(100) NOT NULL DEFAULT ''
    public $archivo_xml; ///< text() NOT NULL DEFAULT ''
    public $archivo_md5; ///< character(32) NOT NULL DEFAULT ''
    public $fecha_hora_respuesta; ///< timestamp without time zone() NULL DEFAULT ''
    public $estado; ///< smallint(16) NULL DEFAULT ''
    public $recepcion_xml; ///< text() NULL DEFAULT ''
    public $recibos_xml; ///< text() NULL DEFAULT ''
    public $resultado_xml; ///< text() NULL DEFAULT ''
    public $usuario; ///< integer(32) NULL DEFAULT '' FK:usuario.id

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'receptor' => array(
            'name'      => 'Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'codigo' => array(
            'name'      => 'Codigo',
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
        'fecha_hora_email' => array(
            'name'      => 'Fecha Hora Email',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'asunto' => array(
            'name'      => 'Asunto',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'de' => array(
            'name'      => 'De',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'responder_a' => array(
            'name'      => 'Responder A',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'mensaje' => array(
            'name'      => 'Mensaje',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'mensaje_html' => array(
            'name'      => 'Mensaje Html',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha_hora_firma' => array(
            'name'      => 'Fecha Hora Firma',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'documentos' => array(
            'name'      => 'Documentos',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'archivo' => array(
            'name'      => 'Archivo',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'archivo_xml' => array(
            'name'      => 'Archivo Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'archivo_md5' => array(
            'name'      => 'Archivo Md5',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha_hora_respuesta' => array(
            'name'      => 'Fecha Hora Respuesta',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'estado' => array(
            'name'      => 'Estado',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'recepcion_xml' => array(
            'name'      => 'Recepcion Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'recibos_xml' => array(
            'name'      => 'Recibos Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'resultado_xml' => array(
            'name'      => 'Resultado Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'usuario' => array(
            'name'      => 'Usuario',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'usuario', 'column' => 'id')
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método que guarda el enviodte que se ha recibido desde otro contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-18
     */
    public function save()
    {
        $this->certificacion = (int)$this->certificacion;
        if (!isset($this->codigo)) {
            // ver si existe una entrada igual
            $existe = (bool)$this->db->getValue('
                SELECT COUNT(*)
                FROM dte_intercambio
                WHERE
                    receptor = :receptor
                    AND certificacion = :certificacion
                    AND fecha_hora_firma = :fecha_hora_firma
                    AND archivo_md5 = :archivo_md5
            ', [
                'receptor' => $this->receptor,
                'certificacion' => $this->certificacion,
                'fecha_hora_firma' => $this->fecha_hora_firma,
                'archivo_md5' => $this->archivo_md5,
            ]);
            if ($existe)
                return true;
            // corregir datos
            $this->archivo = utf8_encode($this->archivo);
            // guardar entrada
            $this->db->beginTransaction(true);
            $this->codigo = (int)$this->db->getValue('
                SELECT MAX(codigo)
                FROM dte_intercambio
                WHERE receptor = :receptor AND certificacion = :certificacion
            ', [':receptor' => $this->receptor, 'certificacion' => $this->certificacion]) + 1;
            try {
                $status = parent::save();
                $this->db->commit();
                return $status;
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                $this->db->rollback();
                throw new \Exception('Error al guardar el archivo \''.$this->archivo.'\' del intercambio enviado por '.$this->de.' con el asunto \''.$this->asunto.'\' del día '.$this->fecha_hora_email.' / '.$e->getMessage());
            }
        } else {
            return parent::save();
        }
    }

    /**
     * Método que entrega el objeto EnvioDte
     * @return \sasco\LibreDTE\Sii\EnvioDte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function getEnvioDte()
    {
        if (!isset($this->EnvioDte)) {
            $this->EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            $this->EnvioDte->loadXML(base64_decode($this->archivo_xml));
        }
        return $this->EnvioDte;
    }

    /**
     * Método que entrega un arreglo con los objetos Dte con los documentos
     * @return Arreglo de \sasco\LibreDTE\Sii\Dte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-11-21
     */
    public function getDocumentos()
    {
        if (!isset($this->Documentos))
            $this->Documentos = $this->getEnvioDte()->getDocumentos(false); // usar saveXML en vez de C14N
        return $this->Documentos;
    }

    /**
     * Método que entrega un objetos Dte con el documento solicitado o false si no se encontró
     * @return \sasco\LibreDTE\Sii\Dte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-11-21
     */
    public function getDocumento($emisor, $dte, $folio)
    {
        return $this->getEnvioDte()->getDocumento($emisor, $dte, $folio);
    }

    /**
     * Método que entrega el objeto del emisor del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function getEmisor()
    {
        if (!isset($this->Emisor)) {
            $this->Emisor = (new Model_Contribuyentes())->get($this->emisor);
            if (!$this->Emisor->exists()) {
                $this->Emisor->dv = \sowerphp\app\Utility_Rut::dv($this->emisor);
                $this->Emisor->razon_social = \sowerphp\app\Utility_Rut::addDV($this->emisor);
                $this->Emisor->save();
            }
        }
        return $this->Emisor;
    }

    /**
     * Método que entrega el objeto del estado del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-27
     */
    public function getEstado()
    {
        if (!isset($this->estado))
            return (object)['estado'=>null];
        return (object)['estado'=>\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$this->estado]];
    }

    /**
     * Método que indica si intercambio se encuentra recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-10
     */
    public function recibido()
    {
        return (bool)$this->db->getValue('
            SELECT COUNT(*)
            FROM dte_recibido
            WHERE receptor = :receptor AND emisor = :emisor AND intercambio = :intercambio
        ', [':receptor'=>$this->receptor, ':emisor'=>$this->emisor, ':intercambio'=>$this->codigo]);
    }

}
