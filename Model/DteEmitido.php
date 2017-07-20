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
 * Clase para mapear la tabla dte_emitido de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_emitido
 * @author SowerPHP Code Generator
 * @version 2015-09-23 11:44:17
 */
class Model_DteEmitido extends Model_Base_Envio
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_emitido'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $folio; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $tasa; ///< smallint(16) NOT NULL DEFAULT '0'
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut
    public $exento; ///< integer(32) NULL DEFAULT ''
    public $neto; ///< integer(32) NULL DEFAULT ''
    public $iva; ///< integer(32) NOT NULL DEFAULT '0'
    public $total; ///< integer(32) NOT NULL DEFAULT ''
    public $usuario; ///< integer(32) NOT NULL DEFAULT '' FK:usuario.id
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< integer(32) NULL DEFAULT ''
    public $revision_estado; ///< character varying(100) NULL DEFAULT ''
    public $revision_detalle; ///< character text() NULL DEFAULT ''
    public $anulado; ///< boolean() NOT NULL DEFAULT 'false'
    public $iva_fuera_plazo; ///< boolean() NOT NULL DEFAULT 'false'
    public $cesion_xml; ///< text() NOT NULL DEFAULT ''
    public $cesion_track_id; ///< integer(32) NULL DEFAULT ''

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
        'anulado' => array(
            'name'      => 'Anulado',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva_fuera_plazo' => array(
            'name'      => 'IVA fuera plazo',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'cesion_xml' => array(
            'name'      => 'Cesion Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'cesion_track_id' => array(
            'name'      => 'Cesion Track Id',
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
        'Model_DteTipo' => 'website\Dte\Admin\Mantenedores',
        'Model_Contribuyente' => 'website\Dte',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    private $Dte; ///< Objeto con el DTE
    private $datos; ///< Arreglo con los datos del XML del DTE
    private $Receptor = null; /// caché para el receptor

    /**
     * Constructor clase DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-09
     */
    public function __construct($emisor = null, $dte = null, $folio = null, $certificacion = null)
    {
        if ($emisor!==null and $dte!==null and $folio!==null and $certificacion!==null) {
            parent::__construct($emisor, $dte, $folio, $certificacion);
            if ($this->revision_estado==-11) {
                $this->revision_detalle = 'Esperando respuesta de SII';
            }
        }
    }

    /**
     * Método que realiza verificaciones a campos antes de guardar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-30
     */
    public function save()
    {
        $this->anulado = (int)$this->anulado;
        $this->iva_fuera_plazo = (int)$this->iva_fuera_plazo;
        parent::save();
    }

    /**
     * Método que entrega el objeto del tipo del dte
     * @return \website\Dte\Admin\Mantenedores\Model_DteTipo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del Dte
     * @return \sasco\LibreDTE\Sii\Dte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function getDte()
    {
        if (!$this->Dte) {
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDte->loadXML(base64_decode($this->xml));
            $this->Dte = $EnvioDte->getDocumentos()[0];
        }
        return $this->Dte;
    }

    /**
     * Método que entrega el objeto del receptor del DTE
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-20
     */
    public function getReceptor()
    {
        if ($this->Receptor === null) {
            $this->Receptor = (new Model_Contribuyentes())->get($this->receptor);
            if (in_array($this->dte, [110, 111, 112])) {
                $datos = $this->getDte()->getDatos()['Encabezado']['Receptor'];
                $this->Receptor->razon_social = $datos['RznSocRecep'];
                $this->Receptor->direccion = !empty($datos['DirRecep']) ? $datos['DirRecep'] : null;
                $this->Receptor->comuna = null;
            }
        }
        return $this->Receptor;
    }

    /**
     * Método que entrega el arreglo con los datos que se usaron para generar el
     * XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function getDatos()
    {
        if (!$this->datos) {
            $this->datos = $this->getDte()->getDatos();
        }
        return $this->datos;
    }

    /**
     * Método que entrega el listado de correos a los que se debería enviar el
     * DTE (correo receptor, correo intercambio y correo del dte)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-02
     */
    public function getEmails()
    {
        $emails = [];
        if ($this->getReceptor()->config_email_intercambio_user) {
            $emails['Email intercambio'] = $this->getReceptor()->config_email_intercambio_user;
        }
        if ($this->getReceptor()->email and !in_array($this->getReceptor()->email, $emails)) {
            $emails['Email receptor'] = $this->getReceptor()->email;
        }
        if ($this->getReceptor()->getUsuario()->email and !in_array($this->getReceptor()->getUsuario()->email, $emails)) {
            $emails['Email usuario administrador'] = $this->getReceptor()->getUsuario()->email;
        }
        if ($this->emisor==\sowerphp\core\Configure::read('libredte.proveedor.rut')) {
            if ($this->getReceptor()->config_app_contacto_comercial) {
                $i = 1;
                foreach($this->getReceptor()->config_app_contacto_comercial as $contacto) {
                    if (!in_array($contacto->email, $emails)) {
                        $emails['Contacto comercial #'.$i++] = $contacto->email;
                    }
                }
            }
        }
        if (!empty($this->getDatos()['Encabezado']['Receptor']['CorreoRecep']) and !in_array($this->getDatos()['Encabezado']['Receptor']['CorreoRecep'], $emails)) {
            $emails['DTE T'.$this->dte.'F'.$this->folio] = $this->getDatos()['Encabezado']['Receptor']['CorreoRecep'];
        }
        if (\sowerphp\core\Module::loaded('Crm')) {
            try {
                $Cliente = new \website\Crm\Model_Cliente($this->getEmisor(), $this->getReceptor()->rut);
                $contactos = $Cliente->getContactos();
                $i = 1;
                foreach ($contactos as $c) {
                    if (!in_array($c['email'], $emails)) {
                        $emails['Correo CRM #'.$i++] = $c['email'];
                    }
                }
            } catch (\Exception $e) {
            }
        }
        return $emails;
    }

    /**
     * Método que entrega las referencias que existen a este DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-26
     */
    public function getReferencias()
    {
        return $this->db->getTable('
            SELECT t.tipo AS documento_tipo, r.folio, d.fecha, rt.tipo AS referencia_tipo, r.razon, r.dte
            FROM
                dte_referencia AS r
                JOIN dte_tipo AS t ON r.dte = t.codigo
                JOIN dte_emitido AS d ON d.emisor= r.emisor AND d.certificacion = r.certificacion AND d.dte = r.dte AND d.folio = r.folio
                LEFT JOIN dte_referencia_tipo AS rt ON r.codigo = rt.codigo
            WHERE
                r.emisor = :rut
                AND r.certificacion = :certificacion
                AND r.referencia_dte = :dte
                AND r.referencia_folio = :folio
            ORDER BY fecha DESC, t.tipo ASC, r.folio DESC
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':folio'=>$this->folio, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega del intercambio el objeto del Recibo del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioRecibo()
    {
        $Recibo = new Model_DteIntercambioReciboDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Recibo->exists() ? $Recibo : false;
    }

    /**
     * Método que entrega del intercambio el objeto de la Recepcion del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioRecepcion()
    {
        $Recepcion = new Model_DteIntercambioRecepcionDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Recepcion->exists() ? $Recepcion : false;
    }

    /**
     * Método que entrega del intercambio el objeto del Resultado del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioResultado()
    {
        $Resultado = new Model_DteIntercambioResultadoDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Resultado->exists() ? $Resultado : false;
    }

    /**
     * Método que entrega los pagos programados del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-28
     */
    public function getPagosProgramados()
    {
        $MntPagos = [];
        if (isset($this->getDatos()['Encabezado']['IdDoc']['MntPagos']) and is_array($this->getDatos()['Encabezado']['IdDoc']['MntPagos'])) {
            $MntPagos = $this->getDatos()['Encabezado']['IdDoc']['MntPagos'];
            if (!isset($MntPagos[0]))
                $MntPagos = [$MntPagos];
            $MntPago = 0;
            foreach ($MntPagos as $pago)
                $MntPago += $pago['MntPago'];
            if ($MntPago!=$this->total)
                $MntPagos = [];
        }
        return $MntPagos;
    }

    /**
     * Método que entrega los datos de cobranza de los pagos programados del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-28
     */
    public function getCobranza()
    {
        return $this->db->getTable('
            SELECT c.fecha, c.monto, c.glosa, c.pagado, c.observacion, u.usuario, c.modificado
            FROM cobranza AS c LEFT JOIN usuario AS u ON c.usuario = u.id
            WHERE
                c.emisor = :rut
                AND c.dte = :dte
                AND c.folio = :folio
                AND c.certificacion = :certificacion
            ORDER BY fecha
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':folio'=>$this->folio, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega el estado del envío del DTE al SII
     * @return R: si es RSC, RCT, RCH, =null otros casos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-07
     */
    public function getEstado()
    {
        $espacio = strpos($this->revision_estado, ' ');
        $estado = $espacio ? substr($this->revision_estado, 0, $espacio) : $this->revision_estado;
        return in_array($estado, Model_DteEmitidos::$revision_estados['rechazados']) ? 'R' : null;
    }

    /**
     * Método que elimina el DTE, y si no hay DTE posterior del mismo tipo,
     * restaura el folio para que se volver a utilizar.
     * Sólo se pueden eliminar DTE que estén rechazados o no enviados al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-01
     */
    public function delete()
    {
        if ($this->track_id and $this->track_id!=-1 and $this->getEstado()!='R') {
            return false;
        }
        $this->db->beginTransaction(true);
        $DteFolio = new \website\Dte\Admin\Model_DteFolio($this->emisor, $this->dte, (int)$this->certificacion);
        if ($DteFolio->siguiente == ($this->folio+1)) {
            $DteFolio->siguiente--;
            $DteFolio->disponibles++;
            try {
                if (!$DteFolio->save(false)) {
                    $this->db->rollback();
                    return false;
                }
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                $this->db->rollback();
                return false;
            }
        }
        if (!parent::delete()) {
            $this->db->rollback();
            return false;
        }
        if (\sowerphp\core\Module::loaded('Pagos')) {
            $Cobro = $this->getCobro(false);
            if ($Cobro->exists()) {
                if (!$Cobro->delete()) {
                    $this->db->rollback();
                    return false;
                }
            }
        }
        $this->db->commit();
        return true;
    }

    /**
     * Método que envía el DTE emitido al SII, básicamente lo saca del sobre y
     * lo pone en uno nuevo con el RUT del SII
     * @param user ID del usuari oque hace el envío
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-05
     */
    public function enviar($user = null)
    {
        $Emisor = $this->getEmisor();
        // boletas no se envían
        if (in_array($this->dte, [39, 41])) {
            return false; // no hay excepción para hacerlo "silenciosamente"
        }
        // si hay track_id y el DTE no está rechazado entonces no se permite
        // volver a enviar al SII (ya que estaría aceptado, aceptado con reparos
        // o aun no se sabe su estado)
        if ($this->track_id and $this->getEstado()!='R') {
            $msg = 'DTE no puede ser reenviado ya que ';
            if (!$this->revision_estado)
                $msg .= 'aun no se ha verificado su estado';
            else if ($this->getEstado()!='R')
                $msg .= 'no está rechazado';
            throw new \Exception($msg);
        }
        // obtener firma
        $Firma = $Emisor->getFirma($user);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar)');
        }
        // generar nuevo sobre
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->agregar($this->getDte());
        $EnvioDte->setFirma($Firma);
        $EnvioDte->setCaratula([
            'RutEnvia' => $Firma ? $Firma->getID() : false,
            'RutReceptor' => '60803000-K',
            'FchResol' => $this->certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' => $this->certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
        ]);
        // generar XML del sobre y "parchar" el DTE
        $xml = $EnvioDte->generar();
        $xml = str_replace(
            ['<DTE xmlns="http://www.sii.cl/SiiDte" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '<SignedInfo>'],
            ['<DTE', '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">'],
            $xml
        );
        // obtener token
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            throw new \Exception('No fue posible obtener el token para el SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        // enviar XML
        $result = \sasco\LibreDTE\Sii::enviar($Firma->getID(), $Emisor->rut.'-'.$Emisor->dv, $xml, $token);
        if ($result===false or $result->STATUS!='0') {
            throw new \Exception('No fue posible enviar el DTE al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        $this->track_id = (int)$result->TRACKID;
        $this->revision_estado = null;
        $this->revision_detalle = null;
        $this->save();
        return $this->track_id;
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII, en realidad
     * es un wrapper para las verdaderas llamadas
     * @param usarWebservice =true se consultará vía servicio web = false vía email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-11
     */
    public function actualizarEstado($user = null, $usarWebservice = true)
    {
        if (!$this->track_id) {
            throw new \Exception('DTE no tiene Track ID, primero debe enviarlo al SII');
        }
        return $usarWebservice ? $this->actualizarEstadoWebservice($user) : $this->actualizarEstadoEmail();
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII a través del
     * servicio web que dispone el SII para esta consulta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-03
     */
    private function actualizarEstadoWebservice($user = null)
    {
        // crear DTE (se debe crear de esta forma y no usar getDatos() ya que se
        // requiere la firma)
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(base64_decode($this->xml));
        $Dte = $EnvioDte->getDocumentos()[0];
        // obtener firma
        $Firma = $this->getEmisor()->getFirma($user);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar)');
        }
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        // solicitar token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            throw new \Exception('No fue posible obtener el token');
        }
        // consultar estado enviado
        $estado_up = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$this->getEmisor()->rut, $this->getEmisor()->dv, $this->track_id, $token]);
        // si el estado no se pudo recuperar error
        if ($estado_up===false) {
            throw new \Exception('No fue posible obtener el estado del DTE');
        }
        // armar estado del dte
        $estado = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0];
        if (isset($estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0]))
            $glosa = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
        else
            $glosa = null;
        $this->revision_estado = $glosa ? ($estado.' - '.$glosa) : $estado;
        $this->revision_detalle = null;
        if ($estado=='EPR') {
            $resultado = (array)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_BODY')[0];
            // DTE aceptado
            if ($resultado['ACEPTADOS']) {
                $this->revision_detalle = 'DTE aceptado';
            }
            // DTE rechazado
            else if ($resultado['RECHAZADOS']) {
                $this->revision_estado = 'RCH - DTE Rechazado';
            }
            // DTE con reparos
            else  {
                $this->revision_estado = 'RLV - DTE Aceptado con Reparos Leves';
            }
        }
        // guardar estado del dte
        try {
            $this->save();
            return [
                'track_id' => $this->track_id,
                'revision_estado' => $this->revision_estado,
                'revision_detalle' => $this->revision_detalle,
            ];
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            throw new \Exception('El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage());
        }
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII a través del
     * email que es recibido desde el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-11
     */
    private function actualizarEstadoEmail()
    {
        // buscar correo con respuesta
        $Imap = $this->getEmisor()->getEmailImap('sii');
        if (!$Imap) {
            throw new \Exception('No fue posible conectar mediante IMAP a '.$this->getEmisor()->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors()));
        }
        $asunto = 'Resultado de Revision Envio '.$this->track_id.' - '.$this->getEmisor()->rut.'-'.$this->getEmisor()->dv;
        $uids = $Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        if (!$uids) {
            if (str_replace('-', '', $this->fecha)<date('Ymd')) {
                $this->solicitarRevision();
                throw new \Exception('No se encontró respuesta de envío del DTE, se solicitó nueva revisión.');
            } else {
                throw new \Exception('No se encontró respuesta de envío del DTE, espere unos segundos o solicite nueva revisión.');
            }
        }
        // procesar emails recibidos
        foreach ($uids as $uid) {
            $estado = $detalle = null;
            $m = $Imap->getMessage($uid);
            if (!$m)
                continue;
            foreach ($m['attachments'] as $file) {
                if ($file['type']!='application/xml')
                    continue;
                $xml = new \SimpleXMLElement($file['data'], LIBXML_COMPACT);
                // obtener estado y detalle
                if (isset($xml->REVISIONENVIO)) {
                    if ($xml->REVISIONENVIO->REVISIONDTE->TIPODTE==$this->dte and $xml->REVISIONENVIO->REVISIONDTE->FOLIO==$this->folio) {
                        $estado = (string)$xml->REVISIONENVIO->REVISIONDTE->ESTADO;
                        $detalle = (string)$xml->REVISIONENVIO->REVISIONDTE->DETALLE;
                    }
                } else {
                    $estado = (string)$xml->IDENTIFICACION->ESTADO;
                    $detalle = (int)$xml->ESTADISTICA->SUBTOTAL->ACEPTA ? 'DTE aceptado' : 'DTE no aceptado';
                }
            }
            if (isset($estado)) {
                $this->revision_estado = $estado;
                $this->revision_detalle = $detalle;
                try {
                    $this->save();
                    $Imap->setSeen($uid);
                    return [
                        'track_id' => $this->track_id,
                        'revision_estado' => $estado,
                        'revision_detalle' => $detalle
                    ];
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    throw new \Exception('El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage());
                }
            }
        }
    }

    /**
     * Método que propone una referencia para el documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-04-27
     */
    public function getPropuestaReferencia()
    {
        // si es factura o boleta se anula con nota crédito
        if (in_array($this->dte, [33, 34, 39, 41, 46, 56])) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 61,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
        // si es nota de crédito se anula con nota de débito
        else if ($this->dte==61) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 56,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
        // si es guía de despacho se factura
        else if ($this->dte==52) {
            return [
                'titulo' => 'Facturar guía',
                'color' => 'success',
                'dte' => 33,
                'codigo' => 0,
                'razon' => 'Se factura',
            ];
        }
        // si es factura de exportación o nota de débito de exportación se anula con nota de crédito exp
        else if (in_array($this->dte, [110, 111])) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 112,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
        // si es nota de crédito de exportación electrónica se anula con nota de débito exp
        else if ($this->dte==112) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 111,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
    }

    /**
     * Método que corrige el monto total del DTE al valor de la moneda oficial
     * para el día según lo registrado en el sistema (datos del banco central)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-20
     */
    public function calcularCLP()
    {
        if (!$this->getDte()->esExportacion())
            return false;
        $moneda = $this->getDte()->getDatos()['Encabezado']['Totales']['TpoMoneda'];
        $total = $this->getDte()->getDatos()['Encabezado']['Totales']['MntTotal'];
        $cambio = (float)(new \sowerphp\app\Sistema\General\Model_MonedaCambio($moneda, 'CLP', $this->fecha))->valor;
        return $cambio ? abs(round($total*$cambio)) : -1;
    }

    /**
     * Método que envía el DTE por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-16
     */
    public function email($to = null, $subject = null, $msg = null, $pdf = false, $cedible = false, $papelContinuo = null)
    {
        $Request = new \sowerphp\core\Network_Request();
        // variables por defecto
        if (!$to)
            $to = $this->getReceptor()->config_email_intercambio_user;
        if (!$to)
            throw new \Exception('No hay correo a quien enviar el DTE');
        if (!is_array($to))
            $to = [$to];
        if (!$subject)
            $subject = 'EnvioDTE: '.num($this->getEmisor()->rut).'-'.$this->getEmisor()->dv.' - '.$this->getTipo()->tipo.' N° '.$this->folio;
        if (!$msg) {
            $msg = 'Se adjunta '.$this->getTipo()->tipo.' N° '.$this->folio.' del día '.\sowerphp\general\Utility_Date::format($this->fecha).' por un monto total de $'.num($this->total).'.-'."\n\n";
            if ($this->getEmisor()->config_pagos_habilitado and $this->getTipo()->operacion=='S') {
                $enlace_pagar_dte = $Request->url.'/pagos/documentos/pagar/'.$this->dte.'/'.$this->folio.'/'.$this->emisor.'/'.$this->fecha.'/'.$this->total;
                $msg .= 'Enlace pago en línea: '.$enlace_pagar_dte."\n\n";
            }
        }
        if ($papelContinuo===null) {
            $papelContinuo = $this->getEmisor()->config_pdf_dte_papel;
        }
        // crear email
        $email = $this->getEmisor()->getEmailSmtp();
        if ($this->getEmisor()->config_pagos_email or $this->getEmisor()->email) {
            $email->replyTo($this->getEmisor()->config_pagos_email ? $this->getEmisor()->config_pagos_email : $this->getEmisor()->email);
        }
        $email->to($to);
        $email->subject($subject);
        // adjuntar PDF
        if ($pdf) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->getEmisor()->getUsuario()->hash);
            $response = $rest->get($Request->url.'/api/dte/dte_emitidos/pdf/'.$this->dte.'/'.$this->folio.'/'.$this->emisor, [
                'cedible' => $cedible,
                'papelContinuo' => $papelContinuo,
                'compress' => false,
            ]);
            if ($response['status']['code']!=200) {
                throw new \Exception($response['body']);
            }
            $email->attach([
                'data' => $response['body'],
                'name' => 'dte_'.$this->getEmisor()->rut.'-'.$this->getEmisor()->dv.'_T'.$this->dte.'F'.$this->folio.'.pdf',
                'type' => 'application/pdf',
            ]);
        }
        // adjuntar XML
        $email->attach([
            'data' => base64_decode($this->xml),
            'name' => 'dte_'.$this->getEmisor()->rut.'-'.$this->getEmisor()->dv.'_T'.$this->dte.'F'.$this->folio.'.xml',
            'type' => 'application/xml',
        ]);
        // enviar email
        $status = $email->send($msg);
        if ($status===true) {
            return true;
        } else {
            throw new \Exception(
                'No fue posible enviar el email: '.$status['message']
            );
        }
    }

    /**
     * Método que entrega el cobro asociado al DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-16
     */
    public function getCobro($crearSiNoExiste = true)
    {
        return (new \website\Pagos\Model_Cobro())->setDocumento($this, $crearSiNoExiste);
    }

    /**
     * Método que entrega el vencimiento del documento si es que existe
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-15
     */
    public function getVencimiento()
    {
        $datos = $this->getDatos();
        return !empty($datos['Encabezado']['IdDoc']['FchVenc']) ? $datos['Encabezado']['IdDoc']['FchVenc'] : null;
    }

    /**
     * Método que entrega el total real del DTE, si es documento de exportación
     * se entrega el total en la moneda extranjera
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-03-11
     */
    public function getTotal()
    {
        if (!in_array($this->dte, [110, 111, 112]))
            return $this->total;
        return $this->getDatos()['Encabezado']['Totales']['MntTotal'];
    }

    /**
     * Método que entrega el detalle del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-06
     */
    public function getDetalle()
    {
        $Detalle = $this->getDatos()['Detalle'];
        return isset($Detalle[0]) ? $Detalle : [$Detalle];
    }

}
