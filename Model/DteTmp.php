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
    private $cache_datos; ///< Caché para los datos del documento

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

    /**
     * Método que crea el DTE real asociado al DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-19
     */
    public function generar($user_id = null)
    {
        $Emisor = $this->getEmisor();
        if (!$user_id)
            $user_id = $Emisor->usuario;
        // obtener firma electrónica
        $Firma = $Emisor->getFirma($user_id);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 506);
        }
        // solicitar folio
        $FolioInfo = $Emisor->getFolio($this->dte);
        if (!$FolioInfo) {
            throw new \Exception('No fue posible obtener un folio para el DTE de tipo '.$this->dte, 508);
        }
        // si el CAF no está vigente se alerta al usuario
        /*if (\sowerphp\general\Utility_Date::countMonths($FolioInfo->Caf->getFechaAutorizacion()) > 18) {
            throw new \Exception('Se obtuvo el CAF para el folio T'.$FolioInfo->DteFolio->dte.'F'.$FolioInfo->folio.', sin embargo el CAF no está vigente (autorizado hace más de 18 meses)', 508);
        }*/
        // si quedan pocos folios y se debe alertar al usuario admnistrador de la empresa se hace
        if ($FolioInfo->DteFolio->disponibles<=$FolioInfo->DteFolio->alerta and !$FolioInfo->DteFolio->alertado) {
            $asunto = 'Alerta de folios tipo '.$FolioInfo->DteFolio->dte;
            $msg = 'Se ha alcanzado el límite de folios del tipo de DTE '.$FolioInfo->DteFolio->dte.' para el contribuyente '.$Emisor->razon_social.', quedan '.$FolioInfo->DteFolio->disponibles.'. Por favor, solicite un nuevo archivo CAF y súbalo a LibreDTE.';
            if ($Emisor->notificar($asunto, $msg)) {
                $FolioInfo->DteFolio->alertado = 1;
                $FolioInfo->DteFolio->save();
            }
        }
        // armar xml a partir del DTE temporal
        $EnvioDte = $this->getEnvioDte($FolioInfo->folio, $FolioInfo->Caf, $Firma);
        if (!$EnvioDte) {
            throw new \Exception('No fue posible generar el objeto del EnvioDTE. Folio '.$FolioInfo->folio.' quedará sin usar.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 510);
        }
        $xml = $EnvioDte->generar();
        if (!$xml or !$EnvioDte->schemaValidate()) {
            throw new \Exception('No fue posible generar el XML del EnvioDTE. Folio '.$FolioInfo->folio.' quedará sin usar.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 510);
        }
        // guardar DTE
        $r = $EnvioDte->getDocumentos()[0]->getResumen();
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $r['TpoDoc'], $r['NroDoc'], (int)$Emisor->config_ambiente_en_certificacion);
        if ($DteEmitido->exists()) {
            throw new \Exception('Ya existe un DTE del tipo '.$r['TpoDoc'].' y folio '.$r['NroDoc'].' emitido', 409);
        }
        $cols = ['tasa'=>'TasaImp', 'fecha'=>'FchDoc', 'sucursal_sii'=>'CdgSIISucur', 'receptor'=>'RUTDoc', 'exento'=>'MntExe', 'neto'=>'MntNeto', 'iva'=>'MntIVA', 'total'=>'MntTotal'];
        foreach ($cols as $attr => $col) {
            if ($r[$col]!==false)
                $DteEmitido->$attr = $r[$col];
        }
        $DteEmitido->receptor = substr($DteEmitido->receptor, 0, -2);
        $DteEmitido->xml = base64_encode($xml);
        $DteEmitido->usuario = $user_id;
        if (in_array($DteEmitido->dte, [110, 111, 112])) {
            $DteEmitido->total = $DteEmitido->exento = $this->total;
        }
        $DteEmitido->anulado = 0;
        $DteEmitido->iva_fuera_plazo = 0;
        $DteEmitido->save();
        // guardar referencias si existen
        $datos = json_decode($this->datos, true);
        if (!empty($datos['Referencia'])) {
            if (!isset($datos['Referencia'][0]))
                $datos['Referencia'] = [$datos['Referencia']];
            foreach ($datos['Referencia'] as $referencia) {
                if (is_numeric($referencia['TpoDocRef']) and $referencia['TpoDocRef']<200) {
                    $DteReferencia = new Model_DteReferencia();
                    $DteReferencia->emisor = $DteEmitido->emisor;
                    $DteReferencia->dte = $DteEmitido->dte;
                    $DteReferencia->folio = $DteEmitido->folio;
                    $DteReferencia->certificacion = $DteEmitido->certificacion;
                    $DteReferencia->referencia_dte = $referencia['TpoDocRef'];
                    $DteReferencia->referencia_folio = $referencia['FolioRef'];
                    $DteReferencia->codigo = !empty($referencia['CodRef']) ? $referencia['CodRef'] : null;
                    $DteReferencia->razon = !empty($referencia['RazonRef']) ? $referencia['RazonRef'] : null;
                    $DteReferencia->save();
                }
            }
        }
        // guardar pagos programados si existen
        $MntPagos = $DteEmitido->getPagosProgramados();
        if (!empty($MntPagos)) {
            foreach ($MntPagos as $pago) {
                $Cobranza = new \website\Dte\Cobranzas\Model_Cobranza();
                $Cobranza->emisor = $DteEmitido->emisor;
                $Cobranza->dte = $DteEmitido->dte;
                $Cobranza->folio = $DteEmitido->folio;
                $Cobranza->certificacion = $DteEmitido->certificacion;
                $Cobranza->fecha = $pago['FchPago'];
                $Cobranza->monto = $pago['MntPago'];
                $Cobranza->glosa = !empty($pago['GlosaPagos']) ? $pago['GlosaPagos'] : null;
                $Cobranza->save();
            }
        }
        // enviar al SII
        try {
            $DteEmitido->enviar($user_id);
        } catch (\Exception $e) {
        }
        // generar cobro si corresponde o actualizar el existe si está pagado
        if ($this->getTipo()->operacion=='S' and $Emisor->config_pagos_habilitado) {
            $Cobro = $this->getCobro(false);
            if (!$Cobro->pagado) {
                if ($Emisor->config_cobros_emitido_automatico) {
                    $DteEmitido->getCobro();
                }
            } else {
                $Cobro->emitido = $DteEmitido->folio;
                $Cobro->save();
            }
        }
        // eliminar DTE temporal
        $this->delete();
        // entregar DTE emitido
        return $DteEmitido;
    }

    /**
     * Método que borra el DTE temporal y su cobro asociado si existe
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-16
     */
    public function delete($borrarCobro = true)
    {
        $this->db->beginTransaction();
        if ($borrarCobro and $this->getEmisor()->config_pagos_habilitado) {
            $Cobro = $this->getCobro(false);
            if ($Cobro->exists() and !$Cobro->pagado) {
                if (!$Cobro->delete(false)) {
                    $this->db->rollback();
                    return false;
                }
            }
        }
        if (!parent::delete()) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return true;
    }

    /**
     * Método que entrega el listado de correos a los que se podría enviar el documento
     * temporal (correo receptor, correo del dte y contacto comercial)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-02
     */
    public function getEmails()
    {
        $emails = [];
        if ($this->getReceptor()->email) {
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
            $emails[$this->getFolio()] = $this->getDatos()['Encabezado']['Receptor']['CorreoRecep'];
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
     * Método que envía el DTE temporal por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-20
     */
    public function email($to = null, $subject = null, $msg = null, $cotizacion = true)
    {
        $Request = new \sowerphp\core\Network_Request();
        // variables por defecto
        if (!$to)
            $to = $this->getEmails();
        if (!$to)
            throw new \Exception('No hay correo a quien enviar el DTE');
        if (!is_array($to))
            $to = [$to];
        if (!$subject)
            $subject = 'Documento N° '.$this->getFolio().' de '.$this->getEmisor()->razon_social.' ('.$this->getEmisor()->getRUT().')';
        if (!$msg) {
            $msg .= 'Se adjunta documento N° '.$this->getFolio().' del día '.\sowerphp\general\Utility_Date::format($this->fecha).' por un monto total de $'.num($this->total).'.-'."\n\n";
            if ($this->getEmisor()->config_pagos_habilitado and $this->getDte()->operacion=='S') {
                $enlace_pagar_cotizacion = $Request->url.'/pagos/cotizaciones/pagar/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor;
                $msg .= 'Enlace pago en línea: '.$enlace_pagar_cotizacion."\n\n";
            }
        }
        // crear email
        $email = $this->getEmisor()->getEmailSmtp();
        $email->to($to);
        if ($this->getEmisor()->config_pagos_email or $this->getEmisor()->email) {
            $email->replyTo($this->getEmisor()->config_pagos_email ? $this->getEmisor()->config_pagos_email : $this->getEmisor()->email);
        }
        $email->subject($subject);
        // adjuntar PDF
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->getEmisor()->getUsuario()->hash);
        if ($cotizacion) {
            $response = $rest->get($Request->url.'/dte/dte_tmps/cotizacion/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor);
        } else {
            $response = $rest->get($Request->url.'/api/dte/dte_tmps/pdf/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor);
        }
        if ($response['status']['code']!=200) {
            throw new \Exception($response['body']);
        }
        $email->attach([
            'data' => $response['body'],
            'name' => ($cotizacion?'cotizacion':'dte_tmp').'_'.$this->getEmisor()->getRUT().'_'.$this->getFolio().'.pdf',
            'type' => 'application/pdf',
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
     * Método que entrega el arreglo con los datos del documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-14
     */
    public function getDatos()
    {
        if (!isset($this->cache_datos)) {
            $this->cache_datos = json_decode($this->datos, true);
        }
        return $this->cache_datos;
    }

    /**
     * Método que entrega el cobro asociado al DTE temporal
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

}
