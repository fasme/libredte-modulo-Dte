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
 * Clase para mapear la tabla dte_tmp de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_tmp
 * @author SowerPHP Code Generator
 * @version 2015-09-22 01:01:43
 */
class Model_DteTmp extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_tmp'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $codigo; ///< character(32) NOT NULL DEFAULT '' PK
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $total; ///< integer(32) NOT NULL DEFAULT ''
    public $datos; ///< text() NOT NULL DEFAULT ''

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
        'codigo' => array(
            'name'      => 'Codigo',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'fecha' => array(
            'name'      => 'Fecha',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'total' => array(
            'name'      => 'Total',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'datos' => array(
            'name'      => 'Datos',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte',
        'Model_DteTipo' => 'website\Dte'
    ); ///< Namespaces que utiliza esta clase

    private $Receptor; ///< Caché para el receptor

    /**
     * Método que genera el XML de EnvioDTE a partir de los datos ya
     * normalizados de un DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-13
     */
    public function getEnvioDte($folio = 0, \sasco\LibreDTE\Sii\Folios $Folios = null, \sasco\LibreDTE\FirmaElectronica $Firma = null, $RutReceptor = null)
    {
        $dte = json_decode($this->datos, true);
        if (!$dte)
            return false;
        $dte['Encabezado']['IdDoc']['Folio'] = $folio;
        $Dte = new \sasco\LibreDTE\Sii\Dte($dte, false);
        if ($Folios and !$Dte->timbrar($Folios))
            return false;
        if ($Firma and !$Dte->firmar($Firma))
            return false;
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->agregar($Dte);
        if ($Firma)
            $EnvioDte->setFirma($Firma);
        $Emisor = $this->getEmisor();
        $EnvioDte->setCaratula([
            'RutEnvia' => $Firma ? $Firma->getID() : false,
            'RutReceptor' => $RutReceptor ? $RutReceptor : $Dte->getReceptor(),
            'FchResol' => $Emisor->config_ambiente_en_certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' => $Emisor->config_ambiente_en_certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
        ]);
        return $EnvioDte;
    }

    /**
     * Método que entrega el objeto de receptor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-05
     */
    public function getReceptor()
    {
        if ($this->Receptor === null) {
            $this->Receptor = (new Model_Contribuyentes())->get($this->receptor);
            if (in_array($this->dte, [110, 111, 112])) {
                $datos = json_decode($this->datos, true)['Encabezado']['Receptor'];
                $this->Receptor->razon_social = $datos['RznSocRecep'];
                $this->Receptor->direccion = $datos['DirRecep'];
                $this->Receptor->comuna = null;
            }
        }
        return $this->Receptor;
    }

    /**
     * Método que entrega el objeto del tipo de dte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-01
     */
    public function getDte()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del tipo de dte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-07
     */
    public function getTipo()
    {
        return $this->getDte();
    }

    /**
     * Método que entrega el objeto del emisor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-02
     */
    public function getEmisor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Método que entrega el folio del documento temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function getFolio()
    {
        return $this->dte.'-'.strtoupper(substr($this->codigo, 0, 7));
    }

}
