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
 * Clase para mapear la tabla dte_recibido de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_recibido
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-12-28
 */
class Model_DteRecibido extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_recibido'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $folio; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut
    public $tasa; ///< smallint(16) NOT NULL DEFAULT '0'
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''
    public $exento; ///< integer(32) NULL DEFAULT ''
    public $neto; ///< integer(32) NULL DEFAULT ''
    public $iva; ///< integer(32) NOT NULL DEFAULT '0'
    public $total; ///< integer(32) NOT NULL DEFAULT ''
    public $usuario; ///< integer(32) NOT NULL DEFAULT '' FK:usuario.id
    public $intercambio; ///< integer(32) NULL DEFAULT ''
    public $iva_uso_comun; ///< integer(32) NULL DEFAULT ''
    public $iva_no_recuperable; ///< text() NULL DEFAULT ''
    public $impuesto_adicional; ///< text() NULL DEFAULT ''
    public $impuesto_tipo; ///< smallint(16) NOT NULL DEFAULT '1'
    public $anulado; ///< character(1) NULL DEFAULT ''
    public $impuesto_sin_credito; ///< integer(32) NULL DEFAULT ''
    public $monto_activo_fijo; ///< integer(32) NULL DEFAULT ''
    public $monto_iva_activo_fijo; ///< integer(32) NULL DEFAULT ''
    public $iva_no_retenido; ///< integer(32) NULL DEFAULT ''
    public $periodo; ///< integer(32) NULL DEFAULT ''
    public $impuesto_puros; ///< integer(32) NULL DEFAULT ''
    public $impuesto_cigarrillos; ///< integer(32) NULL DEFAULT ''
    public $impuesto_tabaco_elaborado; ///< integer(32) NULL DEFAULT ''
    public $impuesto_vehiculos; ///< integer(32) NULL DEFAULT ''
    public $numero_interno; ///< integer(32) NULL DEFAULT ''
    public $emisor_nc_nd_fc; ///< smallint(16) NULL DEFAULT ''
    public $sucursal_sii_receptor; ///< integer(32) NULL DEFAULT ''

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
        'folio' => array(
            'name'      => 'Folio',
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
        'receptor' => array(
            'name'      => 'Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'tasa' => array(
            'name'      => 'Tasa',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
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
        'sucursal_sii' => array(
            'name'      => 'Sucursal Sii',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'exento' => array(
            'name'      => 'Exento',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'neto' => array(
            'name'      => 'Neto',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva' => array(
            'name'      => 'Iva',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '0',
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
        'usuario' => array(
            'name'      => 'Usuario',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'usuario', 'column' => 'id')
        ),
        'intercambio' => array(
            'name'      => 'Intercambio',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva_uso_comun' => array(
            'name'      => 'Iva Uso Comun',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva_no_recuperable' => array(
            'name'      => 'Iva No Recuperable',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_adicional' => array(
            'name'      => 'Impuesto Adicional',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_tipo' => array(
            'name'      => 'Impuesto Tipo',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '1',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'anulado' => array(
            'name'      => 'Anulado',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_sin_credito' => array(
            'name'      => 'Impuesto Sin Credito',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'monto_activo_fijo' => array(
            'name'      => 'Monto Activo Fijo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'monto_iva_activo_fijo' => array(
            'name'      => 'Monto Iva Activo Fijo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva_no_retenido' => array(
            'name'      => 'Iva No Retenido',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'periodo' => array(
            'name'      => 'Período',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_puros' => array(
            'name'      => 'Impuesto Puros',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_cigarrillos' => array(
            'name'      => 'Impuesto Cigarrillos',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_tabaco_elaborado' => array(
            'name'      => 'Impuesto Tabaco Elaborado',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_vehiculos' => array(
            'name'      => 'Impuesto Vehiculos',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'numero_interno' => array(
            'name'      => 'Numero Interno',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'emisor_nc_nd_fc' => array(
            'name'      => 'Emisor Nc Nd Fc',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sucursal_sii_receptor' => array(
            'name'      => 'Sucursal Sii Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
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
        'Model_Contribuyente' => 'website\Dte',
        'Model_DteTipo' => 'website\Dte\Admin\Mantenedores',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios',
        'Model_IvaNoRecuperable' => 'website\Dte\Admin\Mantenedores',
        'Model_ImpuestoAdicional' => 'website\Dte\Admin\Mantenedores'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método que asigna los campos iva_no_recuperable e impuesto_adicional si
     * se pasaron separados en varios campos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-06
     */
    public function set($datos)
    {
        parent::set($datos);
        // asignar iva no recuperable
        $iva_no_recuperable = [];
        if ($datos['iva_no_recuperable_codigo']) {
            $iva_no_recuperable_codigo = explode(',', $datos['iva_no_recuperable_codigo']);
            $iva_no_recuperable_monto = explode(',', $datos['iva_no_recuperable_monto']);
            $n_codigos = count($iva_no_recuperable_codigo);
            for ($i=0; $i<$n_codigos; $i++) {
                $iva_no_recuperable[] = [
                    'codigo' => $iva_no_recuperable_codigo[$i],
                    'monto' => $iva_no_recuperable_monto[$i],
                ];
            }
        }
        $this->iva_no_recuperable = $iva_no_recuperable ? json_encode($iva_no_recuperable) : null;
        // asignar impuesto adicional
        $impuesto_adicional = [];
        if ($datos['impuesto_adicional_codigo']) {
            $impuesto_adicional_codigo = explode(',', $datos['impuesto_adicional_codigo']);
            $impuesto_adicional_tasa = explode(',', $datos['impuesto_adicional_tasa']);
            $impuesto_adicional_monto = explode(',', $datos['impuesto_adicional_monto']);
            $n_codigos = count($impuesto_adicional_codigo);
            for ($i=0; $i<$n_codigos; $i++) {
                $impuesto_adicional[] = [
                    'codigo' => $impuesto_adicional_codigo[$i],
                    'tasa' => $impuesto_adicional_tasa[$i],
                    'monto' => $impuesto_adicional_monto[$i],
                ];
            }
        }
        $this->impuesto_adicional = $impuesto_adicional ? json_encode($impuesto_adicional) : null;
    }

    /**
     * Método para guardar el documento recibido, se hacen algunas validaciones previo a guardar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-28
     */
    public function save()
    {
        // si el emisor no existe con esto se creará
        $this->getEmisor();
        // campo emisor solo en nc y nd
        if (!in_array($this->dte, [55, 56, 60, 61]))
            $this->emisor_nc_nd_fc = null;
        // se guarda el receptor
        return parent::save();
    }

    /**
     * Método que entrega el objeto del tipo del dte
     * @return \website\Dte\Admin\Mantenedores\Model_DteTipo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-27
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del emisor del dte recibido
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
     * Método que entrega el objeto del receptor del dte recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function getReceptor()
    {
        return (new Model_Contribuyentes())->get($this->receptor);
    }

    /**
     * Método que consulta al estado al SII del dte recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-17
     */
    public function getEstado(\sasco\LibreDTE\FirmaElectronica $Firma)
    {
        // obtener token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token)
            return false;
        // consultar estado
        list($RutConsultante, $DvConsultante) = explode('-', $Firma->getID());
        list($Y, $m, $d) = explode('-', $this->fecha);
        $xml = \sasco\LibreDTE\Sii::request('QueryEstDte', 'getEstDte', [
            'RutConsultante'    => $RutConsultante,
            'DvConsultante'     => $DvConsultante,
            'RutCompania'       => $this->getEmisor()->rut,
            'DvCompania'        => $this->getEmisor()->dv,
            'RutReceptor'       => $this->getReceptor()->rut,
            'DvReceptor'        => $this->getReceptor()->dv,
            'TipoDte'           => $this->dte,
            'FolioDte'          => $this->folio,
            'FechaEmisionDte'   => $d.$m.$Y,
            'MontoDte'          => $this->total,
            'token'             => $token,
        ]);
        // si hubo error con el estado se muestra que no se pudo obtener
        if ($xml===false)
            return false;
        return (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
    }

    /**
     * Método que entrega los impuestos adicionales del documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-09
     */
    public function getImpuestosAdicionales($prefix = '')
    {
        if (!$this->impuesto_adicional)
            return [];
        $impuesto_adicional = [];
        foreach (json_decode($this->impuesto_adicional, true) as $ia) {
            $fila = [];
            foreach ($ia as $k => $v) {
                $fila[$prefix.$k] = $v;
            }
            $impuesto_adicional[] = $fila;
        }
        return $impuesto_adicional;
    }

    /**
     * Método que entrega los valores de IVA no recuperable
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-09
     */
    public function getIVANoRecuperable($prefix = '')
    {
        if (!$this->iva_no_recuperable)
            return [];
        $iva_no_recuperable = [];
        foreach (json_decode($this->iva_no_recuperable, true) as $inr) {
            $fila = [];
            foreach ($inr as $k => $v) {
                $fila[$prefix.$k] = $v;
            }
            $iva_no_recuperable[] = $fila;
        }
        return $iva_no_recuperable;
    }

}
