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
 * Clase para mapear la tabla contribuyente de la base de datos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-05-28
 */
class Model_Contribuyente extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'contribuyente'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $rut; ///< integer(32) NOT NULL DEFAULT '' PK
    public $dv; ///< character(1) NOT NULL DEFAULT ''
    public $razon_social; ///< character varying(100) NOT NULL DEFAULT ''
    public $giro; ///< character varying(80) NOT NULL DEFAULT ''
    public $actividad_economica; ///< integer(32) NULL DEFAULT '' FK:actividad_economica.codigo
    public $telefono; ///< character varying(20) NULL DEFAULT ''
    public $email; ///< character varying(80) NULL DEFAULT ''
    public $direccion; ///< character varying(70) NOT NULL DEFAULT ''
    public $comuna; ///< character(5) NOT NULL DEFAULT '' FK:comuna.codigo
    public $usuario; ///< integer(32) NULL DEFAULT '' FK:usuario.id
    public $modificado; ///< timestamp without time zone() NOT NULL DEFAULT 'now()'

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'rut' => array(
            'name'      => 'Rut',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'dv' => array(
            'name'      => 'Dv',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'razon_social' => array(
            'name'      => 'Razon Social',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'giro' => array(
            'name'      => 'Giro',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'actividad_economica' => array(
            'name'      => 'Actividad Economica',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'actividad_economica', 'column' => 'codigo')
        ),
        'telefono' => array(
            'name'      => 'Telefono',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 20,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'email' => array(
            'name'      => 'Email',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'direccion' => array(
            'name'      => 'Direccion',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 70,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'comuna' => array(
            'name'      => 'Comuna',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 5,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'comuna', 'column' => 'codigo')
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
        'modificado' => array(
            'name'      => 'Modificado',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => 'now()',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_ActividadEconomica' => '\website\Sistema\General',
        'Model_Comuna' => '\sowerphp\app\Sistema\General\DivisionGeopolitica',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    public static $encriptar = [
        'email_sii_pass',
        'email_intercambio_pass',
        'api_auth_user',
        'api_auth_pass',
    ]; ///< columnas de la configuración que se deben encriptar para guardar en la base de datos

    public static $defaultConfig = [
        'extra_otras_actividades' => [],
    ]; ///< valores por defecto para columnas de la configuración en caso que no estén especificadas

    private static $reservados = [
        55555555,
        66666666,
        88888888,
    ]; ///< RUTs que están reservados y no serán modificados al guardar el contribuyente

    public $contribuyente; ///< Copia de razon_social
    private $config = null; ///< Caché para configuraciones

    /**
     * Constructor del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-06
     */
    public function __construct($rut = null)
    {
        if (is_array($rut))
            $rut = $rut[0];
        if (!is_numeric($rut) and strpos($rut, '-'))
            $rut = explode('-', str_replace('.', '', $rut))[0];
        parent::__construct(+$rut);
        if ($this->rut and !$this->exists()) {
            $this->dv = \sowerphp\app\Utility_Rut::dv($this->rut);
            $response = \sowerphp\core\Network_Http_Socket::get(
                'https://sasco.cl/api/servicios/enlinea/sii/actividad_economica/'.$this->rut.'/'.$this->dv
            );
            if ($response['status']['code']==200) {
                $info = json_decode($response['body'], true);
                $this->razon_social = substr($info['razon_social'], 0, 100);
                if (!empty($info['actividades'][0]['codigo']))
                    $this->actividad_economica = $info['actividades'][0]['codigo'];
                if (!empty($info['actividades'][0]['glosa']))
                    $this->giro = substr($info['actividades'][0]['glosa'], 0, 80);
                try {
                    $this->save();
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                }
            }
            foreach (['telefono', 'email', 'direccion'] as $attr) {
                if (!$this->$attr)
                    $this->$attr = null;
            }
        }
        $this->contribuyente = &$this->razon_social;
        $this->getConfig();
    }

    /**
     * Método que entrega las configuraciones y parámetros extras para el
     * contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-27
     */
    public function getConfig()
    {
        if ($this->config===false or !$this->rut)
            return null;
        if ($this->config===null) {
            $config = $this->db->getAssociativeArray('
                SELECT configuracion, variable, valor, json
                FROM contribuyente_config
                WHERE contribuyente = :contribuyente
            ', [':contribuyente' => $this->rut]);
            if (!$config) {
                $this->config = false;
                return null;
            }
            foreach ($config as $configuracion => $datos) {
                if (!isset($datos[0]))
                    $datos = [$datos];
                $this->config[$configuracion] = [];
                foreach ($datos as $dato) {
                    if (in_array($configuracion.'_'.$dato['variable'], self::$encriptar)) {
                        $dato['valor'] = Utility_Data::decrypt($dato['valor']);
                    }
                    $this->config[$configuracion][$dato['variable']] =
                        $dato['json'] ? json_decode($dato['valor']) : $dato['valor']
                    ;
                }
            }
        }
        return $this->config;
    }

    /**
     * Método mágico para obtener configuraciones del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-31
     */
    public function __get($name)
    {
        if (strpos($name, 'config_')===0) {
            $this->getConfig();
            $key = str_replace('config_', '', $name);
            $c = substr($key, 0, strpos($key, '_'));
            $v = substr($key, strpos($key, '_')+1);
            if (!isset($this->config[$c][$v]))
                return isset(self::$defaultConfig[$c.'_'.$v]) ? self::$defaultConfig[$c.'_'.$v] : null;
            $this->$name = $this->config[$c][$v];
            return $this->$name;
        } else {
            throw new \Exception(
                'Atributo '.$name.' del contribuyente no existe (no se puede obtener)'
            );
        }
    }

    /**
     * Método mágico asignar una configuración del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-04
     */
    public function __set($name, $value)
    {
        if (strpos($name, 'config_')===0) {
            $key = str_replace('config_', '', $name);
            $c = substr($key, 0, strpos($key, '_'));
            $v = substr($key, strpos($key, '_')+1);
            $value = ($value===false or $value===0) ? '0' : ((!is_array($value) and !is_object($value)) ? (string)$value : ((is_array($value) and empty($value))?null:$value));
            $this->config[$c][$v] = (!is_string($value) or isset($value[0])) ? $value : null;
            $this->$name = $this->config[$c][$v];
        } else {
            throw new \Exception(
                'Atributo '.$name.' del contribuyente no existe (no se puede asignar)'
            );
        }
    }

    /**
     * Método para setear los atributos del contribuyente
     * @param array Arreglo con los datos que se deben asignar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-29
     */
    public function set($array)
    {
        parent::set($array);
        foreach($array as $name => $value) {
            if (strpos($name, 'config_')===0) {
                $this->__set($name, $value);
            }
        }
    }

    /**
     * Método que guarda los datos del contribuyente, incluyendo su
     * configuración y parámetros adicionales
     * @param registrado Se usa para indicar que el contribuyente que se esta guardando es uno registrado por un usuario (se validan otros datos)
     * @param no_modificar =true Evita que se modifiquen ciertos contribuyentes reservados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-05
     */
    public function save($registrado = false, $no_modificar = true)
    {
        // si no se debe guardar se entrega true (se hace creer que se guardó)
        if ($no_modificar and in_array($this->rut, self::$reservados)) {
            return true;
        }
        // si es contribuyente registrado se hacen algunas verificaciones
        if ($registrado) {
            // verificar campos mínimos
            foreach (['razon_social', 'giro', 'actividad_economica', 'direccion', 'comuna'] as $attr) {
                if (empty($this->$attr)) {
                    throw new \Exception('Debe especificar: '.$attr);
                }
            }
            // verificar que si se está en producción se haya pasado la fecha y número de resolución
            if (!$this->config_ambiente_en_certificacion and (empty($this->config_ambiente_produccion_fecha) or empty($this->config_ambiente_produccion_numero))) {
                throw new \Exception('Para usar la empresa en producción debe indicar la fecha y número de resolución que la autoriza');
            }
            if ($this->config_ambiente_en_certificacion and empty($this->config_ambiente_certificacion_fecha)) {
                throw new \Exception('Para usar la empresa en certificación debe indicar la fecha que la autoriza');
            }
            // si se pasó un logo se guarda
            if (isset($_FILES['logo']) and !$_FILES['logo']['error']) {
                if (\sowerphp\general\Utility_File::mimetype($_FILES['logo']['tmp_name'])!='image/png') {
                    throw new \Exception('Formato del logo debe ser PNG');
                }
                $config = \sowerphp\core\Configure::read('dte.logos');
                \sowerphp\general\Utility_Image::resizeOnFile($_FILES['logo']['tmp_name'], $config['width'], $config['height']);
                move_uploaded_file($_FILES['logo']['tmp_name'], $config['dir'].'/'.$this->rut.'.png');
            }
        }
        // corregir datos
        $this->dv = strtoupper($this->dv);
        $this->razon_social = substr($this->razon_social, 0, 100);
        $this->giro = substr($this->giro, 0, 80);
        $this->telefono = substr($this->telefono, 0, 20);
        $this->email = substr($this->email, 0, 80);
        $this->direccion = substr($this->direccion, 0, 70);
        $this->modificado = date('Y-m-d H:i:s');
        // guardar contribuyente
        if (!parent::save())
            return false;
        // guardar configuración
        if ($this->config) {
            foreach ($this->config as $configuracion => $datos) {
                foreach ($datos as $variable => $valor) {
                    $Config = new Model_ContribuyenteConfig($this->rut, $configuracion, $variable);
                    if (!is_array($valor) and !is_object($valor)) {
                        $Config->json = 0;
                    } else {
                        $valor = json_encode($valor);
                        $Config->json = 1;
                    }
                    if (in_array($configuracion.'_'.$variable, self::$encriptar) and $valor!==null) {
                        $valor = Utility_Data::encrypt($valor);
                    }
                    $Config->valor = $valor;
                    if ($valor!==null)
                        $Config->save();
                    else
                        $Config->delete();
                }
            }
        }
        return true;
    }

    /**
     * Método que 'elimina' al contribuyente. En realidad los contribuyentes
     * nunca se eliminan. Lo que se hace es desasociar al contribuyente de su
     * usuario administrador y se elimina la configuración.
     * Los datos del contribuyente de documentos emitidos, recibidos, etc no se
     * eliminan por defecto, se debe solicitar específicamente.
     * @todo Eliminar datos de DTE emitidos, recibidos, consumos, folios, etc (no el contribuyente en si)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-11-16
     */
    public function delete($all = false)
    {
        $this->db->beginTransaction();
        // limpieza general
        $this->config = [
            'email' => ['intercambio_user' => $this->config_email_intercambio_user],
        ];
        $this->usuario = null;
        $this->db->query('DELETE FROM contribuyente_config WHERE contribuyente = :rut', [':rut'=>$this->rut]);
        if (!$this->save()) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        // eliminar todos los registros de la empresa de la base de datos
        if ($all) {
            // TODO
        }
        return true;
    }

    /**
     * Método que envía un correo electrónico al contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-11-14
     */
    public function notificar($asunto, $mensaje, $para = null)
    {
        $email = new \sowerphp\core\Network_Email();
        $email->to($para ? $para : $this->getUsuario()->email);
        $email->subject('['.\sowerphp\core\Configure::read('page.body.title').'] '.$this->getRUT().': '.$asunto);
        $msg = $mensaje."\n\n".'-- '."\n".\sowerphp\core\Configure::read('page.body.title');
        return $email->send($msg) === true ? true : false;
    }

    /**
     * Método que entrega el RUT formateado del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function getRUT()
    {
        return num($this->rut).'-'.$this->dv;
    }

    /**
     * Método que entrega la glosa del ambiente en el que se encuentra el
     * contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function getAmbiente()
    {
        return $this->config_ambiente_en_certificacion ? 'certificación' : 'producción';
    }

    /**
     * Método que entrega las actividades económicas del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-08
     */
    public function getListActividades()
    {
        $actividades = [$this->actividad_economica];
        if ($this->config_extra_otras_actividades) {
            foreach ($this->config_extra_otras_actividades as $a) {
                $actividades[] = is_object($a) ? $a->actividad : $a;
            }
        }
        $where = [];
        $vars = [];
        foreach ($actividades as $key => $a) {
            $where[] = ':a'.$key;
            $vars[':a'.$key] = $a;
        }
        return $this->db->getAssociativeArray('
            SELECT codigo, actividad_economica
            FROM actividad_economica
            WHERE codigo IN ('.implode(',', $where).')
            ORDER BY actividad_economica
        ', $vars);
    }

    /**
     * Método que entrega el listado de giros del contribuyente por cada
     * actividad económmica que tiene registrada
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-08
     */
    public function getListGiros()
    {
        $giros = [$this->actividad_economica => $this->giro];
        if ($this->config_extra_otras_actividades) {
            foreach ($this->config_extra_otras_actividades as $a) {
                $giros[is_object($a) ? $a->actividad : $a] = is_object($a) ? ($a->giro?$a->giro:$this->giro) : $this->giro;
            }
        }
        return $giros;
    }

    /**
     * Método que asigna los usuarios autorizados a operar con el contribuyente
     * @param usuarios Arreglo con índice nombre de usuario y valores un arreglo con los permisos a asignar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function setUsuarios(array $usuarios) {
        $this->db->beginTransaction();
        $this->db->query(
            'DELETE FROM contribuyente_usuario WHERE contribuyente = :rut',
            [':rut'=>$this->rut]
        );
        foreach ($usuarios as $usuario => $permisos) {
            $Usuario = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($usuario);
            if (!$Usuario->exists()) {
                $this->db->rollback();
                throw new \Exception('Usuario '.$usuario.' no existe');
                return false;
            }
            foreach ($permisos as $permiso) {
                $ContribuyenteUsuario = new Model_ContribuyenteUsuario($this->rut, $Usuario->id, $permiso);
                $ContribuyenteUsuario->save();
            }
        }
        $this->db->commit();
        return true;
    }

    /**
     * Método que entrega el listado de usuarios autorizados y sus permisos
     * @return Tabla con los usuarios y sus permisos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function getUsuarios()
    {
        return $this->db->getTable('
            SELECT u.usuario, c.permiso
            FROM usuario AS u, contribuyente_usuario AS c
            WHERE u.id = c.usuario AND c.contribuyente = :rut
        ', [':rut'=>$this->rut]);
    }

    /**
     * Método que entrega el listado de usuarios para los campos select
     * @return Listado de usuarios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-03
     */
    public function getListUsuarios()
    {
        return $this->db->getTable('
            SELECT u.id, u.usuario
            FROM usuario AS u, contribuyente_usuario AS c
            WHERE u.id = c.usuario AND c.contribuyente = :rut
        ', [':rut'=>$this->rut]);
    }

    /**
     * Método que determina si el usuario está o no autorizado a trabajar con el
     * contribuyente
     * @param Usuario Objeto \sowerphp\app\Sistema\Usuarios\Model_Usuario con el usuario a verificar
     * @param permisos Permisos que se desean verificar que tenga el usuario
     * @return =true si está autorizado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-12
     */
    public function usuarioAutorizado(\sowerphp\app\Sistema\Usuarios\Model_Usuario $Usuario, $permisos = [])
    {
        // si es el administrador de la empresa se le autoriza
        if ($Usuario->id == $this->usuario)
            return true;
        // normalizar permisos
        if (!is_array($permisos))
            $permisos = [$permisos];
        // si la aplicación sólo tiene configurada una empresa se verifican los
        // permisos normales (basados en grupos) de sowerphp
        if (\sowerphp\core\Configure::read('dte.empresa')) {
            foreach ($permisos as $permiso) {
                if ($Usuario->auth($permiso))
                    return true;
            }
            return false;
        }
        // se busca si el usuario es parte de los que pueden trabajar con el
        // contribuyente, se valida el permiso en particular que se esté
        // pidiendo y el permiso 'todos' (es como validar a sysadmin en Auth)
        if (!in_array('todos', $permisos))
            $permisos[] = 'todos';
        $vars = [':rut'=>$this->rut, ':usuario'=>$Usuario->id];
        $permisos_bind = [];
        foreach ($permisos as $i => $permiso) {
            $permisos_bind[] = ':permiso'.$i;
            $vars[':permiso'.$i] = $permiso;
        }
        $autorizado = (bool)$this->db->getValue('
            SELECT COUNT(*)
            FROM contribuyente_usuario
            WHERE
                contribuyente = :rut
                AND usuario = :usuario
                AND permiso IN ('.implode(', ', $permisos_bind).')
        ', $vars);
        if ($autorizado) {
            return true;
        }
        // ver si el usuario es del grupo de soporte
        if ($this->config_app_soporte and $Usuario->inGroup(['soporte'])) {
            return true;
        }
        // si no se logró determinar el permiso no se autoriza
        return false;
    }

    /**
     * Método que entrega los documentos que el contribuyente tiene autorizados
     * a emitir en la aplicación
     * @return Listado de documentos autorizados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-11
     */
    public function getDocumentosAutorizados($onlyPK = false)
    {
        if ($onlyPK) {
            return $this->db->getCol('
                SELECT t.codigo
                FROM dte_tipo AS t, contribuyente_dte AS c
                WHERE t.codigo = c.dte AND c.contribuyente = :rut AND c.activo = :activo
            ', [':rut'=>$this->rut, ':activo'=>1]);
        } else {
            return $this->db->getTable('
                SELECT t.codigo, t.tipo
                FROM dte_tipo AS t, contribuyente_dte AS c
                WHERE t.codigo = c.dte AND c.contribuyente = :rut AND c.activo = :activo
            ', [':rut'=>$this->rut, ':activo'=>1]);
        }
    }

    /**
     * Método que determina si el documento puede o no ser emitido por el
     * contribuyente a través de la aplicación
     * @param dte Código del DTE que se quiere saber si está autorizado
     * @return =true si está autorizado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-06
     */
    public function documentoAutorizado($dte)
    {
        return (bool)$this->db->getValue('
            SELECT COUNT(*)
            FROM contribuyente_dte
            WHERE contribuyente = :rut AND dte = :dte AND activo = :activo
        ', [':rut'=>$this->rut, ':dte'=>$dte, ':activo'=>1]);
    }

    /**
     * Método que entrega el listado de folios que el Contribuyente dispone
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-25
     */
    public function getFolios()
    {
        return $this->db->getTable('
            SELECT f.dte, t.tipo, f.siguiente, f.disponibles, f.alerta
            FROM dte_folio AS f, dte_tipo AS t
            WHERE f.dte = t.codigo AND emisor = :rut AND f.certificacion = :certificacion
            ORDER BY f.dte
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
    }

    /**
     * Método que entrega el folio siguiente del tipo de documento solicitado
     * para el ambiente que la empresa está operando
     * @param dte Tipo de documento para el cual se quiere su folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function getFolio($dte)
    {
        if (!$this->db->beginTransaction(true))
            return false;
        $DteFolio = new \website\Dte\Admin\Model_DteFolio($this->rut, $dte, (int)$this->config_ambiente_en_certificacion);
        if (!$DteFolio->exists() or !$DteFolio->disponibles) {
            $this->db->rollback();
            return false;
        }
        $folio = $DteFolio->siguiente;
        $DteFolio->siguiente++;
        $DteFolio->disponibles--;
        try {
            if (!$DteFolio->save(false)) {
                $this->db->rollback();
                return false;
            }
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->db->rollback();
            return false;
        }
        $Caf = $this->getCaf($dte, $folio);
        if (!$Caf) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return (object)[
            'folio' => $folio,
            'Caf' => $Caf,
            'DteFolio' => $DteFolio,
        ];
    }

    /**
     * Método que entrega el CAF de un folio de cierto DTE
     * @param dte Tipo de documento para el cual se quiere su CAF
     * @param folio Folio del CAF del DTE que se busca
     * @return \sasco\LibreDTE\Sii\Folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function getCaf($dte, $folio)
    {
        $caf = $this->db->getValue('
            SELECT xml
            FROM dte_caf
            WHERE
                emisor = :rut
                AND dte = :dte
                AND certificacion = :certificacion
                AND :folio BETWEEN desde AND hasta
        ', [
            ':rut' => $this->rut,
            ':dte' => $dte,
            ':certificacion' => (int)$this->config_ambiente_en_certificacion,
            ':folio' => $folio,
        ]);
        if (!$caf)
            return false;
        $caf = Utility_Data::decrypt($caf);
        if (!$caf)
            return false;
        $Caf = new \sasco\LibreDTE\Sii\Folios($caf);
        return $Caf->getTipo() ? $Caf : false;
    }

    /**
     * Método que entrega una tabla con los datos de las firmas electrónicas de
     * los usuarios que están autorizados a trabajar con el contribuyente
     * @param dte Tipo de documento para el cual se quiere su folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function getFirmas()
    {
        return $this->db->getTable('
            (
                SELECT f.run, f.nombre, f.email, f.desde, f.hasta, f.emisor, u.usuario, true AS administrador
                FROM firma_electronica AS f, usuario AS u, contribuyente AS c
                WHERE f.usuario = u.id AND f.usuario = c.usuario AND c.rut = :rut
            ) UNION (
                SELECT f.run, f.nombre, f.email, f.desde, f.hasta, f.emisor, u.usuario, false AS administrador
                FROM firma_electronica AS f, usuario AS u, contribuyente_usuario AS c
                WHERE f.usuario = u.id AND f.usuario = c.usuario AND c.contribuyente = :rut
            )
            ORDER BY administrador DESC, nombre ASC
        ', [':rut'=>$this->rut]);
    }

    /**
     * Método que entrega el objeto de la firma electronica asociada al usuario
     * que la está solicitando o bien aquella firma del usuario que es el
     * administrador del contribuyente.
     * @param user ID del usuario que desea obtener la firma
     * @return \sasco\LibreDTE\FirmaElectronica
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-12
     */
    public function getFirma($user = null)
    {
        // buscar firma del usuario administrador de la empresa
        $datos = $this->db->getRow('
            SELECT f.archivo, f.contrasenia
            FROM firma_electronica AS f, contribuyente AS c
            WHERE f.usuario = c.usuario AND c.rut = :rut
        ', [':rut'=>$this->rut]);
        // buscar firma del usuario que está haciendo la solicitud
        if (empty($datos) and $user and $user!=$this->usuario) {
            $datos = $this->db->getRow('
                SELECT archivo, contrasenia
                FROM firma_electronica
                WHERE usuario = :usuario
            ', [':usuario'=>$user]);
        }
        if (empty($datos))
            return false;
        // si se obtuvo una firma se trata de usar
        $pass = Utility_Data::decrypt($datos['contrasenia']);
        if (!$pass)
            return false;
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'data' => base64_decode($datos['archivo']),
                'pass' => $pass,
            ]);
            return $Firma;
        } catch (\sowerphp\core\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Método que entrega el listado de documentos emitidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getDocumentosEmitidos($filtros = [])
    {
        // armar filtros
        $where = ['d.emisor = :rut', 'd.certificacion = :certificacion'];
        $vars = [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion];
        foreach (['dte', 'folio', 'receptor', 'fecha', 'total', 'usuario'] as $c) {
            if (!empty($filtros[$c])) {
                $where[] = 'd.'.$c.' = :'.$c;
                $vars[':'.$c] = $filtros[$c];
            }
        }
        // si se debe hacer búsqueda dentro de los XML
        if (!empty($filtros['xml'])) {
            $i = 1;
            foreach ($filtros['xml'] as $nodo => $valor) {
                $nodo = preg_replace('/[^A-Za-z\/]/', '', $nodo);
                $where[] = 'LOWER('.$this->db->xml('d.xml', '/EnvioDTE/SetDTE/DTE/*/'.$nodo, 'http://www.sii.cl/SiiDte').') LIKE :xml'.$i;
                $vars[':xml'.$i] = '%'.strtolower($valor).'%';
                $i++;
            }
        }
        // otros filtros
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'd.fecha >= :fecha_desde';
            $vars[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'd.fecha <= :fecha_hasta';
            $vars[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['total_desde'])) {
            $where[] = 'd.total >= :total_desde';
            $vars[':total_desde'] = $filtros['total_desde'];
        }
        if (!empty($filtros['total_hasta'])) {
            $where[] = 'd.total <= :total_hasta';
            $vars[':total_hasta'] = $filtros['total_hasta'];
        }
        if (isset($filtros['sucursal_sii']) and $filtros['sucursal_sii']!=-1) {
            if ($filtros['sucursal_sii']) {
                $where[] = 'd.sucursal_sii = :sucursal_sii';
                $vars[':sucursal_sii'] = $filtros['sucursal_sii'];
            } else {
                $where[] = 'd.sucursal_sii IS NULL';
            }
        }
        // forma de obtener razón social
        $razon_social_xpath = $this->db->xml('d.xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/RznSocRecep', 'http://www.sii.cl/SiiDte');
        // armar consulta
        $query = '
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                CASE WHEN d.dte NOT IN (110, 111, 112) THEN r.razon_social ELSE '.$razon_social_xpath.' END AS razon_social,
                d.fecha,
                d.total,
                d.revision_estado AS estado,
                i.glosa AS intercambio,
                d.sucursal_sii,
                u.usuario
            FROM
                dte_emitido AS d LEFT JOIN dte_intercambio_resultado_dte AS i
                    ON i.emisor = d.emisor AND i.dte = d.dte AND i.folio = d.folio AND i.certificacion = d.certificacion,
                dte_tipo AS t,
                contribuyente AS r,
                usuario AS u
            WHERE d.dte = t.codigo AND d.receptor = r.rut AND d.usuario = u.id AND '.implode(' AND ', $where).'
            ORDER BY d.fecha DESC, t.tipo, d.folio DESC
        ';
        // armar límite consulta
        if (isset($filtros['limit'])) {
            $query = $this->db->setLimit($query, $filtros['limit'], $filtros['offset']);
        }
        // entregar consulta
        return $this->db->getTable($query, $vars);
    }

    /**
     * Método que entrega el total de documentos emitidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-03
     */
    public function countDocumentosEmitidos($filtros = [])
    {
        $where = ['d.emisor = :rut', 'd.certificacion = :certificacion'];
        $vars = [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion];
        foreach (['dte', 'folio', 'receptor', 'fecha', 'total', 'usuario'] as $c) {
            if (isset($filtros[$c])) {
                $where[] = 'd.'.$c.' = :'.$c;
                $vars[':'.$c] = $filtros[$c];
            }
        }
        return $this->db->getValue(
            'SELECT COUNT(*) FROM dte_emitido AS d WHERE '.implode(' AND ', $where),
            $vars
        );
    }

    /**
     * Método que crea el objeto email para enviar por SMTP y lo entrega
     * @param email Email que se quiere obteber: intercambio o sii
     * @return \sowerphp\core\Network_Email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-22
     */
    public function getEmailSmtp($email = 'intercambio')
    {
	$name = $this->config_extra_nombre_fantasia ? $this->config_extra_nombre_fantasia : $this->razon_social;
        return new \sowerphp\core\Network_Email([
            'type' => 'smtp',
            'host' => $this->{'config_email_'.$email.'_smtp'},
            'user' => $this->{'config_email_'.$email.'_user'},
            'pass' => $this->{'config_email_'.$email.'_pass'},
            'from' => ['email'=>$this->{'config_email_'.$email.'_user'}, 'name'=>str_replace(',', '', $name)],
        ]);
    }

    /**
     * Método que crea el objeto Imap para recibir correo por IMAP
     * @param email Email que se quiere obteber: intercambio o sii
     * @return \sowerphp\core\Network_Email_Imap
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-27
     */
    public function getEmailImap($email = 'intercambio')
    {
        $Imap = new \sowerphp\core\Network_Email_Imap([
            'mailbox' => $this->{'config_email_'.$email.'_imap'},
            'user' => $this->{'config_email_'.$email.'_user'},
            'pass' => $this->{'config_email_'.$email.'_pass'},
        ]);
        return $Imap->isConnected() ? $Imap : false;
    }

    /**
     * Método que entrega el resumen de las boletas por períodos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getResumenBoletasPeriodos()
    {
        $periodo_col = $this->db->date('Ym', 'fecha');
        return $this->db->getTable('
            SELECT '.$periodo_col.' AS periodo, COUNT(*) AS emitidas
            FROM dte_emitido
            WHERE emisor = :rut AND certificacion = :certificacion AND dte IN (39, 41)
            GROUP BY '.$periodo_col.'
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
    }

    /**
     * Método que entrega las boletas de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getBoletas($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'e.fecha');
        return $this->db->getTable('
            SELECT
                e.dte,
                e.folio,
                e.fecha,
                '.$this->db->concat('r.rut', '-', 'r.dv').' AS rut,
                e.exento,
                e.total,
                a.codigo AS anulada
            FROM
                dte_emitido AS e
                LEFT JOIN dte_referencia AS a ON
                    a.emisor = e.emisor
                    AND a.referencia_dte = e.dte
                    AND a.referencia_folio = e.folio
                    AND a.certificacion = e.certificacion
                    AND a.codigo = 1,
                contribuyente AS r
            WHERE
                e.receptor = r.rut
                AND e.emisor = :rut
                AND e.certificacion = :certificacion
                AND e.dte IN (39, 41)
                AND '.$periodo_col.' = :periodo
            ORDER BY e.fecha, e.dte, e.folio
        ', [
            ':rut' => $this->rut,
            ':certificacion' => (int)$this->config_ambiente_en_certificacion,
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Método que entrega los documentos para el reporte de consumo de folios de
     * las boletas electrónicas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function getDocumentosConsumoFolios($desde, $hasta = null)
    {
        if (!$hasta)
            $hasta = $desde;
        return $this->db->getTable('
            (
                SELECT
                    dte,
                    folio,
                    tasa,
                    fecha,
                    exento,
                    neto,
                    iva,
                    total
                FROM
                    dte_emitido AS e
                WHERE
                    fecha BETWEEN :desde AND :hasta
                    AND emisor = :rut
                    AND certificacion = :certificacion
                    AND dte IN (39, 41)
            ) UNION (
                SELECT
                    e.dte,
                    e.folio,
                    e.tasa,
                    e.fecha,
                    e.exento,
                    e.neto,
                    e.iva,
                    e.total
                FROM
                    dte_referencia AS r
                    JOIN dte_emitido AS e ON
                        r.emisor = e.emisor
                        AND r.dte = e.dte
                        AND r.folio = e.folio
                        AND r.certificacion = e.certificacion
                WHERE
                    r.emisor = :rut
                    AND r.dte = 61
                    AND r.certificacion = :certificacion
                    AND r.referencia_dte IN (39, 41)
                    AND e.fecha BETWEEN :desde AND :hasta
            )
            ORDER BY fecha, dte, folio
        ', [
            ':rut' => $this->rut,
            ':certificacion' => (int)$this->config_ambiente_en_certificacion,
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega el resumen de las ventas por períodos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getResumenVentasPeriodos()
    {
        $periodo_col = $this->db->date('Ym', 'e.fecha', 'INTEGER');
        return $this->db->getTable('
            (
                SELECT '.$periodo_col.' AS periodo, COUNT(*) AS emitidos, v.documentos AS enviados, v.track_id, v.revision_estado
                FROM dte_tipo AS t, dte_emitido AS e LEFT JOIN dte_venta AS v ON e.emisor = v.emisor AND e.certificacion = v.certificacion AND '.$periodo_col.' = v.periodo
                WHERE t.codigo = e.dte AND t.venta = true AND e.emisor = :rut AND e.certificacion = :certificacion AND e.dte != 46
                GROUP BY '.$periodo_col.', enviados, v.track_id, v.revision_estado
            ) UNION (
                SELECT periodo, documentos AS emitidos, documentos AS enviados, track_id, revision_estado
                FROM dte_venta
                WHERE emisor = :rut AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
    }

    /**
     * Método que entrega las ventas de un período
     * @todo Corregir ID en Extranjero y asignar los NULL por los valores que corresponden (quizás haya que modificar tabla dte_emitido)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-01
     */
    public function getVentas($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'e.fecha');
        $razon_social_xpath = $this->db->xml('e.xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/RznSocRecep', 'http://www.sii.cl/SiiDte');
        $razon_social = 'CASE WHEN e.dte NOT IN (110, 111, 112) THEN r.razon_social ELSE '.$razon_social_xpath.' END AS razon_social';
        // si el contribuyente tiene impuestos adicionales se crean las query para esos campos
        if ($this->config_extra_impuestos_adicionales) {
            list($impuesto_codigo, $impuesto_tasa, $impuesto_monto) = $this->db->xml('e.xml', [
                '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/TipoImp',
                '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/TasaImp',
                '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/MontoImp',
            ], 'http://www.sii.cl/SiiDte');
        } else {
            $impuesto_codigo = $impuesto_tasa = $impuesto_monto = 'NULL';
        }
        // campos para datos extranjeros
        list($extranjero_id, $extranjero_nacionalidad) = $this->db->xml('e.xml', [
            '/EnvioDTE/SetDTE/DTE/Exportaciones/Referencia/FolioRef', // FIXME
            '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/Extranjero/Nacionalidad',
        ], 'http://www.sii.cl/SiiDte');
        $extranjero_id = 'NULL'; // TODO: fix xpath para seleccionar la referencia que tiene codigo 813 (u otro doc identidad que se defina)
        // realizar consulta
        return $this->db->getTable('
            SELECT
                e.dte,
                e.folio,
                '.$this->db->concat('r.rut', '-', 'r.dv').' AS rut,
                e.tasa,
                '.$razon_social.',
                e.fecha,
                CASE WHEN e.anulado THEN \'A\' ELSE NULL END AS anulado,
                e.exento,
                e.neto,
                e.iva,
                CASE WHEN e.iva_fuera_plazo THEN e.iva ELSE NULL END AS iva_fuera_plazo,
                '.$impuesto_codigo.' AS impuesto_codigo,
                '.$impuesto_tasa.' AS impuesto_tasa,
                '.$impuesto_monto.' AS impuesto_monto,
                NULL AS iva_propio,
                NULL AS iva_terceros,
                NULL AS iva_retencion_total,
                NULL AS iva_retencion_parcial,
                NULL AS iva_no_retenido,
                NULL AS ley_18211,
                NULL AS credito_constructoras,
                NULL AS referencia_tipo,
                NULL AS referencia_folio,
                NULL AS deposito_envases,
                NULL AS monto_no_facturable,
                NULL AS monto_periodo,
                NULL AS pasaje_nacional,
                NULL AS pasaje_internacional,
                CASE WHEN e.dte IN (110, 111, 112) THEN '.$extranjero_id.' ELSE NULL END AS extranjero_id,
                CASE WHEN e.dte IN (110, 111, 112) THEN '.$extranjero_nacionalidad.' ELSE NULL END AS extranjero_nacionalidad,
                NULL AS indicador_servicio,
                NULL AS indicador_sin_costo,
                NULL AS liquidacion_rut,
                NULL AS liquidacion_comision_neto,
                NULL AS liquidacion_comision_exento,
                NULL AS liquidacion_comision_iva,
                e.sucursal_sii,
                NULL AS numero_interno,
                NULL AS emisor_nc_nd_fc,
                e.total
            FROM dte_tipo AS t, dte_emitido AS e, contribuyente AS r
            WHERE t.codigo = e.dte AND t.venta = true AND e.receptor = r.rut AND e.emisor = :rut AND e.certificacion = :certificacion AND '.$periodo_col.' = :periodo AND e.dte != 46
            ORDER BY e.fecha, e.dte, e.folio
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el objeto del libro de ventas a partir de las ventas registradas en la aplicación
     * @param periodo Período para el cual se está construyendo el libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-06
     */
    public function getLibroVentas($periodo)
    {
        $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        $ventas = $this->getVentas($periodo);
        foreach ($ventas as $venta) {
            // armar detalle para agregar al libro
            $d = [];
            foreach ($venta as $k => $v) {
                if (strpos($k, 'impuesto_')!==0) {
                    if ($v!==null)
                        $d[Model_DteVenta::$libro_cols[$k]] = $v;
                }
            }
            // agregar otros impuestos
            if (!empty($venta['impuesto_codigo'])) {
                $d['OtrosImp'] = [
                    'CodImp' => $venta['impuesto_codigo'],
                    'TasaImp' => $venta['impuesto_tasa'],
                    'MntImp' => $venta['impuesto_monto'],
                ];
            }
            // agregar al libro
            $Libro->agregar($d);
        }
        return $Libro;
    }

    /**
     * Método que entrega el resumen de las ventas diarias de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getVentasDiarias($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'e.fecha');
        $dia_col = $this->db->date('d', 'e.fecha');
        return $this->db->getAssociativeArray('
            SELECT '.$dia_col.' AS dia, COUNT(*) AS documentos
            FROM dte_tipo AS t, dte_emitido AS e
            WHERE t.codigo = e.dte AND t.venta = true AND e.emisor = :rut AND e.certificacion = :certificacion AND '.$periodo_col.' = :periodo AND e.dte != 46
            GROUP BY e.fecha
            ORDER BY e.fecha
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de ventas por tipo de un período
     * @return Arreglo asociativo con las ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getVentasPorTipo($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'e.fecha');
        return $this->db->getAssociativeArray('
            SELECT t.tipo, COUNT(*) AS ventas
            FROM dte_tipo AS t, dte_emitido AS e
            WHERE t.codigo = e.dte AND t.venta = true AND e.emisor = :rut AND e.certificacion = :certificacion AND '.$periodo_col.' = :periodo AND e.dte != 46
            GROUP BY t.tipo
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de las guías por períodos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getResumenGuiasPeriodos()
    {
        $periodo_col = $this->db->date('Ym', 'e.fecha', 'INTEGER');
        return $this->db->getTable('
            (
                SELECT '.$periodo_col.' AS periodo, COUNT(*) AS emitidos, g.documentos AS enviados, g.track_id, g.revision_estado
                FROM dte_emitido AS e LEFT JOIN dte_guia AS g ON e.emisor = g.emisor AND e.certificacion = g.certificacion AND '.$periodo_col.' = g.periodo
                WHERE e.emisor = :rut AND e.certificacion = :certificacion AND e.dte = 52
                GROUP BY '.$periodo_col.', enviados, g.track_id, g.revision_estado
            ) UNION (
                SELECT periodo, documentos AS emitidos, documentos AS enviados, track_id, revision_estado
                FROM dte_guia
                WHERE emisor = :rut AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
    }

    /**
     * Método que entrega el resumen de las guías de un período
     * @todo Extraer IndTraslado en MariaDB
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getGuias($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'e.fecha');
        $tipo_col= $this->db->xml('e.xml', '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/IdDoc/IndTraslado', 'http://www.sii.cl/SiiDte');
        return $this->db->getTable('
            SELECT
                e.folio,
                CASE WHEN e.anulado THEN 2 ELSE NULL END AS anulado,
                1 AS operacion,
                '.$tipo_col.' AS tipo,
                e.fecha,
                '.$this->db->concat('r.rut', '-', 'r.dv').' AS rut,
                r.razon_social,
                e.neto,
                e.tasa,
                e.iva,
                e.total,
                NULL AS modificado,
                ref.dte AS ref_dte,
                ref.folio AS ref_folio,
                re.fecha AS ref_fecha
            FROM
                dte_emitido AS e
                LEFT JOIN dte_referencia AS ref ON e.emisor = ref.emisor AND e.dte = ref.referencia_dte AND e.folio = ref.referencia_folio AND e.certificacion = ref.certificacion
                LEFT JOIN dte_emitido AS re ON re.emisor = ref.emisor AND re.dte = ref.dte AND re.folio = ref.folio AND re.certificacion = ref.certificacion,
                contribuyente AS r
            WHERE e.receptor = r.rut AND e.emisor = :rut AND e.certificacion = :certificacion AND '.$periodo_col.' = :periodo AND e.dte = 52
            ORDER BY e.fecha, e.folio
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de las guías diarias de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getGuiasDiarias($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'fecha');
        $dia_col = $this->db->date('d', 'fecha');
        return $this->db->getAssociativeArray('
            SELECT '.$dia_col.' AS dia, COUNT(*) AS documentos
            FROM dte_emitido
            WHERE emisor = :rut AND certificacion = :certificacion AND '.$periodo_col.' = :periodo AND dte = 52
            GROUP BY fecha
            ORDER BY fecha
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega la tabla con los casos de intercambio del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getIntercambios($soloPendientes = true)
    {
        $documentos = $this->db->xml('i.archivo_xml', '/*/SetDTE/DTE/Documento/Encabezado/IdDoc/TipoDTE|/*/SetDTE/DTE/Documento/Encabezado/IdDoc/Folio', 'http://www.sii.cl/SiiDte');
        $select = $soloPendientes ? '' : ', i.estado, u.usuario';
        $where = $soloPendientes ? ' AND i.estado IS NULL' : '';
        $intercambios = $this->db->getTable('
            SELECT i.codigo, i.emisor, e.razon_social, i.fecha_hora_firma, i.fecha_hora_email, '.$documentos.' AS documentos'.$select.'
            FROM dte_intercambio AS i LEFT JOIN contribuyente AS e ON i.emisor = e.rut LEFT JOIN usuario AS u ON i.usuario = u.id
            WHERE i.receptor = :receptor AND i.certificacion = :certificacion '.$where.'
            ORDER BY i.fecha_hora_firma DESC
        ', [':receptor'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
        foreach ($intercambios as &$i) {
            if (!empty($i['razon_social']))
                $i['emisor'] = $i['razon_social'];
            if (isset($i['estado']))
                $i['estado'] = \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$i['estado']];
            $nuevo_dte = true;
            $n_letras = strlen($i['documentos']);
            for ($j=0; $j<$n_letras; $j++) {
                if ($i['documentos'][$j]==',') {
                    $nuevo_dte = !$nuevo_dte;
                    if ($nuevo_dte)
                        $i['documentos'][$j] = '|';
                }
            }
            unset($i['razon_social']);
        }
        return $intercambios;
    }

    /**
     * Método que entrega el listado de documentos recibidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-03
     */
    public function getDocumentosRecibidos($filtros = [])
    {
        // armar filtros
        $where = ['d.receptor = :rut', 'd.certificacion = :certificacion'];
        $vars = [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion];
        foreach (['dte', 'folio', 'emisor', 'fecha', 'total', 'intercambio', 'usuario'] as $c) {
            if (isset($filtros[$c])) {
                $where[] = 'd.'.$c.' = :'.$c;
                $vars[':'.$c] = $filtros[$c];
            }
        }
        // armar consulta
        $query = '
            SELECT d.dte, t.tipo, d.folio, e.razon_social, d.fecha, d.total, d.intercambio, u.usuario, d.emisor
            FROM dte_recibido AS d, dte_tipo AS t, contribuyente AS e, usuario AS u
            WHERE d.dte = t.codigo AND d.emisor = e.rut AND d.usuario = u.id AND '.implode(' AND ', $where).'
            ORDER BY d.fecha DESC, t.tipo, e.razon_social
        ';
        // armar límite consulta
        if (isset($filtros['limit'])) {
            $query = $this->db->setLimit($query, $filtros['limit'], $filtros['offset']);
        }
        // entregar consulta
        return $this->db->getTable($query, $vars);
    }

    /**
     * Método que entrega el total de documentos recibidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-03
     */
    public function countDocumentosRecibidos($filtros = [])
    {
        $where = ['d.receptor = :rut', 'd.certificacion = :certificacion'];
        $vars = [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion];
        foreach (['dte', 'folio', 'emisor', 'fecha', 'total', 'usuario'] as $c) {
            if (isset($filtros[$c])) {
                $where[] = 'd.'.$c.' = :'.$c;
                $vars[':'.$c] = $filtros[$c];
            }
        }
        return $this->db->getValue(
            'SELECT COUNT(*) FROM dte_recibido AS d WHERE '.implode(' AND ', $where),
            $vars
        );
    }

    /**
     * Método que entrega el resumen de las compras por períodos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getResumenComprasPeriodos()
    {
        if ($this->db->config['type']!='PostgreSQL')
            return $this->getResumenComprasPeriodosMySQL();
        $periodo_col = $this->db->date('Ym', 'r.fecha', 'INTEGER');
        return $this->db->getTable('
            (
                SELECT
                    CASE WHEN r.periodo IS NOT NULL THEN
                        r.periodo
                    ELSE
                        CASE WHEN f.periodo IS NOT NULL THEN
                            f.periodo
                        ELSE
                            NULL
                        END
                    END AS periodo,
                    CASE WHEN r.recibidos IS NOT NULL AND f.facturas_compra IS NOT NULL THEN
                        r.recibidos + f.facturas_compra
                    ELSE
                        CASE WHEN r.recibidos IS NOT NULL THEN
                            r.recibidos
                        ELSE
                            f.facturas_compra
                        END
                    END AS recibidos,
                    c.documentos AS enviados,
                    c.track_id,
                    c.revision_estado
                FROM
                    (
                        SELECT periodo, COUNT(*) AS recibidos
                        FROM (
                            SELECT
                                CASE WHEN r.periodo IS NOT NULL THEN
                                    r.periodo
                                ELSE
                                    '.$periodo_col.'
                                END AS periodo
                            FROM dte_tipo AS t, dte_recibido AS r
                            WHERE t.codigo = r.dte AND t.compra = true AND r.receptor = :rut AND r.certificacion = :certificacion
                        ) AS t
                        GROUP BY periodo
                    ) AS r
                    FULL JOIN (
                        SELECT '.$periodo_col.' AS periodo, COUNT(*) AS facturas_compra
                        FROM dte_emitido AS r
                        WHERE r.emisor = :rut AND r.certificacion = :certificacion AND r.dte = 46
                        GROUP BY '.$periodo_col.'
                    ) AS f ON r.periodo = f.periodo
                    LEFT JOIN dte_compra AS c ON c.receptor = :rut AND c.certificacion = :certificacion AND c.periodo IN (r.periodo, f.periodo)
            ) UNION (
                SELECT periodo, documentos AS recibidos, documentos AS enviados, track_id, revision_estado
                FROM dte_compra
                WHERE receptor = :rut AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
    }

    /**
     * Método que entrega el resumen de las compras por períodos
     * @warning Versión del método para MySQL, no soporta facturas de compra (se hace en método aparte porque no hay FULL JOIN en MySQL)
     * @todo Emular FULL JOIN para obtener el soporte para facturas de compra
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    private function getResumenComprasPeriodosMySQL()
    {
        $periodo_col = $this->db->date('Ym', 'r.fecha');
        return $this->db->getTable('
            (
                SELECT '.$periodo_col.' AS periodo, COUNT(*) AS recibidos, c.documentos AS enviados, c.track_id, c.revision_estado
                FROM dte_tipo AS t, dte_recibido AS r LEFT JOIN dte_compra AS c ON r.receptor = c.receptor AND r.certificacion = c.certificacion AND '.$periodo_col.' = c.periodo
                WHERE t.codigo = r.dte AND t.compra = true AND r.receptor = :rut AND r.certificacion = :certificacion
                GROUP BY '.$periodo_col.', enviados, c.track_id, c.revision_estado
            ) UNION (
                SELECT periodo, documentos AS recibidos, documentos AS enviados, track_id, revision_estado
                FROM dte_compra
                WHERE receptor = :rut AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
    }

    /**
     * Método que entrega el resumen de las compras de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getCompras($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'r.fecha', 'INTEGER');
        list($impuesto_codigo, $impuesto_tasa, $impuesto_monto) = $this->db->xml('r.xml', [
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/TipoImp',
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/TasaImp',
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/MontoImp',
        ], 'http://www.sii.cl/SiiDte');
        $compras = $this->db->getTable('
            (
                SELECT
                    r.dte,
                    r.folio,
                    '.$this->db->concat('e.rut', '-', 'e.dv').' AS rut,
                    r.tasa,
                    e.razon_social,
                    r.impuesto_tipo,
                    r.fecha,
                    r.anulado,
                    r.exento,
                    r.neto,
                    r.iva,
                    r.iva_no_recuperable,
                    NULL AS iva_no_recuperable_codigo,
                    NULL AS iva_no_recuperable_monto,
                    r.iva_uso_comun,
                    r.impuesto_adicional,
                    NULL AS impuesto_adicional_codigo,
                    NULL AS impuesto_adicional_tasa,
                    NULL AS impuesto_adicional_monto,
                    r.impuesto_sin_credito,
                    r.monto_activo_fijo,
                    r.monto_iva_activo_fijo,
                    r.iva_no_retenido,
                    r.impuesto_puros,
                    r.impuesto_cigarrillos,
                    r.impuesto_tabaco_elaborado,
                    r.impuesto_vehiculos,
                    r.sucursal_sii,
                    r.numero_interno,
                    r.emisor_nc_nd_fc,
                    r.total
                FROM dte_tipo AS t, dte_recibido AS r, contribuyente AS e
                WHERE
                    t.codigo = r.dte
                    AND t.compra = true
                    AND r.emisor = e.rut
                    AND r.receptor = :rut
                    AND r.certificacion = :certificacion
                    AND ((r.periodo IS NULL AND '.$periodo_col.' = :periodo) OR (r.periodo IS NOT NULL AND r.periodo = :periodo))
            ) UNION (
                SELECT
                    r.dte,
                    r.folio,
                    '.$this->db->concat('e.rut', '-', 'e.dv').' AS rut,
                    r.tasa,
                    e.razon_social,
                    NULL AS impuesto_tipo,
                    r.fecha,
                    NULL AS anulado,
                    r.exento,
                    r.neto,
                    r.iva,
                    NULL AS iva_no_recuperable,
                    NULL AS iva_no_recuperable_codigo,
                    NULL AS iva_no_recuperable_monto,
                    NULL AS iva_uso_comun,
                    NULL AS impuesto_adicional,
                    '.$impuesto_codigo.' AS impuesto_adicional_codigo,
                    '.$impuesto_tasa.' AS impuesto_adicional_tasa,
                    '.$impuesto_monto.' AS impuesto_adicional_monto,
                    NULL AS impuesto_sin_credito,
                    NULL AS monto_activo_fijo,
                    NULL AS monto_iva_activo_fijo,
                    NULL AS iva_no_retenido,
                    NULL AS impuesto_puros,
                    NULL AS impuesto_cigarrillos,
                    NULL AS impuesto_tabaco_elaborado,
                    NULL AS impuesto_vehiculos,
                    NULL AS sucursal_sii,
                    NULL AS numero_interno,
                    NULL AS emisor_nc_nd_fc,
                    r.total
                FROM dte_emitido AS r, contribuyente AS e
                WHERE r.receptor = e.rut AND r.emisor = :rut AND r.certificacion = :certificacion AND '.$periodo_col.' = :periodo AND r.dte = 46
            )
            ORDER BY fecha, dte, folio
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
        // procesar cada compra
        foreach ($compras as &$c) {
            // asignar IVA no recuperable
            if ($c['iva_no_recuperable']) {
                $iva_no_recuperable = json_decode($c['iva_no_recuperable'], true);
                $iva_no_recuperable_codigo = [];
                $iva_no_recuperable_monto = [];
                foreach ($iva_no_recuperable as $inr) {
                    $iva_no_recuperable_codigo[] = $inr['codigo'];
                    $iva_no_recuperable_monto[] = $inr['monto'];
                    $c['iva'] -= $inr['monto'];
                }
                $c['iva_no_recuperable_codigo'] = implode(',', $iva_no_recuperable_codigo);
                $c['iva_no_recuperable_monto'] = implode(',', $iva_no_recuperable_monto);
            }
            unset($c['iva_no_recuperable']);
            // asignar monto de impuesto adicionl
            if ($c['impuesto_adicional']) {
                $impuesto_adicional = json_decode($c['impuesto_adicional'], true);
                $impuesto_adicional_codigo = [];
                $impuesto_adicional_tasa = [];
                $impuesto_adicional_monto = [];
                foreach ($impuesto_adicional as $ia) {
                    $impuesto_adicional_codigo[] = $ia['codigo'];
                    $impuesto_adicional_tasa[] = $ia['tasa'];
                    $impuesto_adicional_monto[] = $ia['monto'];
                }
                $c['impuesto_adicional_codigo'] = implode(',', $impuesto_adicional_codigo);
                $c['impuesto_adicional_tasa'] = implode(',', $impuesto_adicional_tasa);
                $c['impuesto_adicional_monto'] = implode(',', $impuesto_adicional_monto);
            }
            unset($c['impuesto_adicional']);
            // asignar factor de proporcionalidad
            $c['iva_uso_comun_factor'] = $c['iva_uso_comun'] ? round(($c['iva_uso_comun']/$c['iva'])*100) : null;
        }
        return $compras;
    }

    /**
     * Método que entrega el objeto del libro de compras a partir de las compras registradas en la aplicación
     * @param periodo Período para el cual se está construyendo el libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-06
     */
    public function getLibroCompras($periodo)
    {
        $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        $compras = $this->getCompras($periodo);
        foreach ($compras as $compra) {
            // armar detalle para agregar al libro
            $d = [];
            foreach ($compra as $k => $v) {
                if (strpos($k, 'impuesto_adicional')!==0 and strpos($k, 'iva_no_recuperable')!==0) {
                    if ($v!==null)
                        $d[Model_DteCompra::$libro_cols[$k]] = $v;
                }
            }
            // agregar iva no recuperable
            if (!empty($compra['iva_no_recuperable_codigo'])) {
                $d['IVANoRec'] = [
                    'CodIVANoRec' => $compra['iva_no_recuperable_codigo'],
                    'MntIVANoRec' => $compra['iva_no_recuperable_monto'],
                ];
            }
            // agregar otros impuestos
            if (!empty($compra['impuesto_adicional_codigo'])) {
                $d['OtrosImp'] = [
                    'CodImp' => $compra['impuesto_adicional_codigo'],
                    'TasaImp' => $compra['impuesto_adicional_tasa'] ? $compra['impuesto_adicional_tasa'] : 0,
                    'MntImp' => $compra['impuesto_adicional_monto'],
                ];
            }
            // agregar detalle al libro
            $Libro->agregar($d);
        }
        return $Libro;
    }

    /**
     * Método que entrega el resumen de las compras diarias de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getComprasDiarias($periodo)
    {
        if ($this->db->config['type']!='PostgreSQL')
            return $this->getComprasDiariasMySQL($periodo);
        $periodo_col = $this->db->date('Ym', 'r.fecha');
        $dia_col = $this->db->date('d', 'r.fecha');
        return $this->db->getAssociativeArray('
            SELECT
                CASE WHEN r.dia IS NOT NULL THEN
                    r.dia
                ELSE
                    CASE WHEN f.dia IS NOT NULL THEN
                        f.dia
                    ELSE
                        NULL
                    END
                END AS dia,
                CASE WHEN r.documentos IS NOT NULL AND f.documentos IS NOT NULL THEN
                    r.documentos + f.documentos
                ELSE
                    CASE WHEN r.documentos IS NOT NULL THEN
                        r.documentos
                    ELSE
                        f.documentos
                    END
                END AS documentos
            FROM
                (
                    SELECT '.$dia_col.' AS dia, COUNT(*) AS documentos
                    FROM dte_tipo AS t, dte_recibido AS r
                    WHERE t.codigo = r.dte AND t.compra = true AND r.receptor = :rut AND r.certificacion = :certificacion AND '.$periodo_col.' = :periodo
                    GROUP BY r.fecha
                ) AS r
                FULL JOIN
                (
                    SELECT '.$dia_col.' AS dia, COUNT(*) AS documentos
                    FROM dte_emitido AS r
                    WHERE r.emisor = :rut AND r.certificacion = :certificacion AND '.$periodo_col.' = :periodo AND r.dte = 46
                    GROUP BY r.fecha
                ) AS f ON r.dia = f.dia
            ORDER BY dia
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de las compras diarias de un período
     * @warning Versión del método para MySQL, no soporta facturas de compra (se hace en método aparte porque no hay FULL JOIN en MySQL)
     * @todo Emular FULL JOIN para obtener el soporte para facturas de compra
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    private function getComprasDiariasMySQL($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'r.fecha');
        $dia_col = $this->db->date('d', 'r.fecha');
        return $this->db->getAssociativeArray('
            SELECT '.$dia_col.' AS dia, COUNT(*) AS documentos
            FROM dte_tipo AS t, dte_recibido AS r
            WHERE t.codigo = r.dte AND t.compra = true AND r.receptor = :rut AND r.certificacion = :certificacion AND '.$periodo_col.' = :periodo
            GROUP BY r.fecha
            ORDER BY r.fecha
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de compras por tipo de un período
     * @return Arreglo asociativo con las compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getComprasPorTipo($periodo)
    {
        $periodo_col = $this->db->date('Ym', 'r.fecha');
        return $this->db->getAssociativeArray('
            (
                SELECT t.tipo, COUNT(*) AS compras
                FROM dte_tipo AS t, dte_recibido AS r
                WHERE t.codigo = r.dte AND t.compra = true AND r.receptor = :rut AND r.certificacion = :certificacion AND '.$periodo_col.' = :periodo
                GROUP BY t.tipo
            ) UNION (
                SELECT t.tipo, COUNT(*) AS facturas_compra
                FROM dte_tipo AS t, dte_emitido AS r
                WHERE t.codigo = r.dte AND r.emisor = :rut AND r.certificacion = :certificacion AND r.dte = 46 AND '.$periodo_col.' = :periodo
                GROUP BY t.tipo
            )
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método para actualizar la bandeja de intercambio. Guarda los DTEs
     * recibidos por intercambio y guarda los acuses de recibos de DTEs
     * enviados por otros contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-07
     */
    public function actualizarBandejaIntercambio()
    {
        $Imap = $this->getEmailImap();
        if (!$Imap) {
            throw new \sowerphp\core\Exception(
                'No fue posible conectar mediante IMAP a '.$this->config_email_intercambio_imap.', verificar mailbox, usuario y/o contraseña de correo de intercambio:<br/>'.implode('<br/>', imap_errors()), 500
            );
        }
        // obtener mensajes sin leer
        $uids = $Imap->search();
        if (!$uids) {
            throw new \sowerphp\core\Exception('No hay documentos nuevos que procesar', 204);
        }
        // procesar cada mensaje sin leer
        $n_EnvioDTE = $n_acuse = $n_EnvioRecibos = $n_RecepcionEnvio = $n_ResultadoDTE = 0;
        foreach ($uids as &$uid) {
            $m = $Imap->getMessage($uid, ['subtype'=>['PLAIN', 'HTML', 'XML'], 'extension'=>['xml']]);
            if ($m and isset($m['attachments'][0])) {
                $datos_email = [
                    'fecha_hora_email' => date('Y-m-d H:i:s', strtotime($m['header']->date)),
                    'asunto' => !empty($m['header']->subject) ? substr($m['header']->subject, 0, 100) : 'Sin asunto',
                    'de' => substr($m['header']->from[0]->mailbox.'@'.$m['header']->from[0]->host, 0, 80),
                    'mensaje' => $m['body']['plain'] ? base64_encode($m['body']['plain']) : null,
                    'mensaje_html' => $m['body']['html'] ? base64_encode($m['body']['html']) : null,
                ];
                if (isset($m['header']->reply_to[0])) {
                    $datos_email['responder_a'] = substr($m['header']->reply_to[0]->mailbox.'@'.$m['header']->reply_to[0]->host, 0, 80);
                }
                $acuseContado = false;
                $procesado = false;
                foreach ($m['attachments'] as $file) {
                    if ($this->actualizarBandejaIntercambio_procesar_EnvioDTE($this->rut, $datos_email, $file)) {
                        $n_EnvioDTE++;
                        $procesado = true;
                    }
                    else if ($this->actualizarBandejaIntercambio_procesar_EnvioRecibos($this, $datos_email, $file)) {
                        $n_EnvioRecibos++;
                        if (!$acuseContado) {
                            $acuseContado = true;
                            $n_acuse++;
                        }
                        $procesado = true;
                    }
                    else if ($this->actualizarBandejaIntercambio_procesar_RecepcionEnvio($this, $datos_email, $file)) {
                        $n_RecepcionEnvio++;
                        if (!$acuseContado) {
                            $acuseContado = true;
                            $n_acuse++;
                        }
                        $procesado = true;
                    }
                    else if ($this->actualizarBandejaIntercambio_procesar_ResultadoDTE($this, $datos_email, $file)) {
                        $n_ResultadoDTE++;
                        if (!$acuseContado) {
                            $acuseContado = true;
                            $n_acuse++;
                        }
                        $procesado = true;
                    }
                }
                // marcar email como leído si fue procesado
                if ($procesado)
                    $Imap->setSeen($uid);
            }
        }
        $n_uids = count($uids);
        $omitidos = $n_uids - $n_EnvioDTE - $n_acuse;
        return compact('n_uids', 'omitidos', 'n_EnvioDTE', 'n_EnvioRecibos', 'n_RecepcionEnvio', 'n_ResultadoDTE');
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente
     * @param receptor RUT del receptor sin puntos ni dígito verificador
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html
     * @param file Arreglo con los índices: name, data, size y type
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-10
     */
    private function actualizarBandejaIntercambio_procesar_EnvioDTE($receptor, array $datos_email, array $file)
    {
        // preparar datos
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        if (!$EnvioDte->loadXML($file['data']) or !$EnvioDte->getID() or $EnvioDte->esBoleta())
            return null;
        $caratula = $EnvioDte->getCaratula();
        if (((int)(bool)!$caratula['NroResol'])!=$this->config_ambiente_en_certificacion)
            return false;
        if (substr($caratula['RutReceptor'], 0, -2) != $receptor)
            return false;
        if (!isset($caratula['SubTotDTE'][0]))
            $caratula['SubTotDTE'] = [$caratula['SubTotDTE']];
        $documentos = 0;
        foreach($caratula['SubTotDTE'] as $SubTotDTE) {
            $documentos += $SubTotDTE['NroDTE'];
        }
        $datos_enviodte = [
            'certificacion' => (int)(bool)!$caratula['NroResol'],
            'emisor' => substr($caratula['RutEmisor'], 0, -2),
            'fecha_hora_firma' => date('Y-m-d H:i:s', strtotime($caratula['TmstFirmaEnv'])),
            'documentos' => $documentos,
            'archivo' => $file['name'],
            'archivo_xml' => base64_encode($file['data']),
        ];
        $datos_enviodte['archivo_md5'] = md5($datos_enviodte['archivo_xml']);
        // guardar envío de intercambio
        $DteIntercambio = new Model_DteIntercambio();
        $DteIntercambio->set($datos_email + $datos_enviodte);
        $DteIntercambio->receptor = $receptor;
        return $DteIntercambio->save();
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente
     * @param Emisor Objeto del emisor del documento que se espera
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html
     * @param file Arreglo con los índices: name, data, size y type
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    private function actualizarBandejaIntercambio_procesar_EnvioRecibos($Emisor, array $datos_email, array $file)
    {
        return (new Model_DteIntercambioRecibo())->saveXML($Emisor, $file['data']);
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente
     * @param Emisor Objeto del emisor del documento que se espera
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html
     * @param file Arreglo con los índices: name, data, size y type
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    private function actualizarBandejaIntercambio_procesar_RecepcionEnvio($Emisor, array $datos_email, array $file)
    {
        return (new Model_DteIntercambioRecepcion())->saveXML($Emisor, $file['data']);
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente
     * @param Emisor Objeto del emisor del documento que se espera
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html
     * @param file Arreglo con los índices: name, data, size y type
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    private function actualizarBandejaIntercambio_procesar_ResultadoDTE($Emisor, array $datos_email, array $file)
    {
        return (new Model_DteIntercambioResultado())->saveXML($Emisor, $file['data']);
    }

    /**
     * Método que entrega el listado de documentos electrónicos que han sido
     * generados pero no se han enviado al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-22
     */
    public function getDteEmitidosSinEnviar($certificacion = null)
    {
	$certificacion = (int)($certificacion !== null ? $certificacion : $this->config_ambiente_en_certificacion);
        return $this->db->getTable('
            SELECT dte, folio
            FROM dte_emitido
            WHERE
                emisor = :rut
                AND certificacion = :certificacion
                AND dte NOT IN (39, 41)
                AND track_id IS NULL
        ', [':rut'=>$this->rut, ':certificacion'=>$certificacion]);
    }

    /**
     * Método que entrega el listado de documentos electrónicos que han sido
     * generados y enviados al SII pero aun no se ha actualizado su estado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-22
     */
    public function getDteEmitidosSinEstado($certificacion = null)
    {
	$certificacion = (int)($certificacion !== null ? $certificacion : $this->config_ambiente_en_certificacion);
        return $this->db->getTable('
            SELECT dte, folio
            FROM dte_emitido
            WHERE
                emisor = :rut
                AND certificacion = :certificacion
                AND dte NOT IN (39, 41)
                AND track_id IS NOT NULL
                AND (revision_estado IS NULL OR revision_estado = \'-11\')
        ', [':rut'=>$this->rut, ':certificacion'=>$certificacion]);
    }

    /**
     * Método que entrega el listado de sucursales del contribuyente con los
     * codigos de actividad económica asociados a cada una (uno por sucursal)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-08
     */
    public function getSucursalesActividades()
    {
        $actividades = [0 => $this->actividad_economica];
        if ($this->config_extra_sucursales) {
            foreach ($this->config_extra_sucursales as $sucursal) {
                $actividades[$sucursal->codigo] = $sucursal->actividad_economica ? $sucursal->actividad_economica : $this->actividad_economica;
            }
        }
        return $actividades;
    }

    /**
     * Método que entrega el listado de sucursales del contribuyente (se incluye
     * la casa matriz)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-13
     */
    public function getSucursales()
    {
        $sucursales = [0=>'Casa matriz ('.$this->direccion.', '.$this->getComuna()->comuna.')'];
        if ($this->config_extra_sucursales) {
            foreach ($this->config_extra_sucursales as $sucursal) {
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->get($sucursal->comuna)->comuna;
                $sucursales[$sucursal->codigo] = $sucursal->sucursal.' ('.$sucursal->direccion.', '.$comuna.')';
            }
        }
        return $sucursales;
    }

    /**
     * Método que entrega el objeto de la sucursal del contribuyente a partir
     * del código de la sucursal (por defecto casa matriz)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-13
     */
    public function getSucursal($codigo = null)
    {
        // si se pasó código se busca sucursal
        if ($codigo and $this->config_extra_sucursales) {
            foreach ($this->config_extra_sucursales as $sucursal) {
                if ($sucursal->codigo == $codigo) {
                    return $sucursal;
                }
            }
        }
        // si no se pasó código o no se encontró se entrega sucursal matriz
        return (object)[
            'codigo' => 0,
            'sucursal' => 'Casa matriz',
            'direccion' => $this->direccion,
            'comuna' => $this->comuna,
        ];
    }

    /**
     * Método que entrega el listado de clientes del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-18
     */
    public function getClientes()
    {
        return $this->db->getTable('
            SELECT c.rut, c.dv, c.razon_social, c.telefono, c.email, c.direccion, co.comuna
            FROM
                contribuyente AS c
                LEFT JOIN comuna AS co ON co.codigo = c.comuna
            WHERE c.rut IN (SELECT receptor FROM dte_emitido WHERE emisor = :emisor)
            ORDER BY c.razon_social
        ', [':emisor'=>$this->rut]);
    }

    /**
     * Método que entrega la cuota de documentos asignada al contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-06
     */
    public function getCuota()
    {
        if ($this->config_libredte_cuota)
            return $this->config_libredte_cuota;
        return \sowerphp\core\Configure::read('dte.cuota');
    }

    /**
     * Método que entrega los documentos usados por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getDocumentosUsados()
    {
        $periodo_col = $this->db->date('Ym', 'fecha');
        $datos = $this->db->getTable('
            SELECT e.periodo, e.total AS emitidos, r.total AS recibidos
            FROM
                (
                    SELECT '.$periodo_col.' AS periodo, COUNT(*) AS total
                    FROM dte_emitido
                    WHERE emisor = :rut AND certificacion = :certificacion
                    GROUP BY '.$periodo_col.'
                ) AS e
                LEFT JOIN
                (
                    SELECT '.$periodo_col.' AS periodo, COUNT(*) AS total
                    FROM dte_recibido
                    WHERE receptor = :rut AND certificacion = :certificacion
                    GROUP BY '.$periodo_col.'
                ) AS r ON e.periodo = r.periodo
            ORDER BY e.periodo DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
        $cuota = $this->getCuota();
        foreach ($datos as &$d) {
            $d['total'] = $d['emitidos'] + $d['recibidos'];
            $d['sobre_cuota'] = ($cuota and ($d['total']-$cuota)>0) ? $d['total']-$cuota : null;
        }
        return $datos;
    }

    /**
     * Método que entrega el resumen de los estados de los DTE para un periodo de tiempo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-23
     */
    public function getDocumentosEmitidosResumenEstados($desde, $hasta)
    {
        return $this->db->getTable('
            SELECT revision_estado AS estado, COUNT(*) AS total
            FROM dte_emitido
            WHERE emisor = :rut AND certificacion = :certificacion AND fecha BETWEEN :desde AND :hasta AND track_id > 0
            GROUP BY revision_estado
            ORDER BY total DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
    }

    /**
     * Método que entrega el detalle de los documentos emitidos con cierto
     * estado en un rango de tiempo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getDocumentosEmitidosEstado($desde, $hasta, $estado = null)
    {
        // filtros
        $vars = [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta];
        if ($estado) {
            $vars[':estado'] = $estado;
            $estado = 'd.revision_estado = :estado';
        } else {
            $estado = 'd.revision_estado IS NULL';
        }
        // forma de obtener razón social
        $razon_social_xpath = $this->db->xml('d.xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/RznSocRecep', 'http://www.sii.cl/SiiDte');
        $razon_social = 'CASE WHEN d.dte NOT IN (110, 111, 112) THEN r.razon_social ELSE '.$razon_social_xpath.' END AS razon_social';
        // realizar consulta
        return $this->db->getTable('
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                '.$razon_social.',
                d.fecha,
                d.total,
                d.revision_detalle AS estado_detalle,
                i.glosa AS intercambio,
                d.sucursal_sii,
                u.usuario
            FROM
                dte_emitido AS d LEFT JOIN dte_intercambio_resultado_dte AS i
                    ON i.emisor = d.emisor AND i.dte = d.dte AND i.folio = d.folio AND i.certificacion = d.certificacion,
                dte_tipo AS t,
                contribuyente AS r,
                usuario AS u
            WHERE
                d.dte = t.codigo
                AND d.receptor = r.rut
                AND d.usuario = u.id
                AND d.emisor = :rut
                AND d.certificacion = :certificacion
                AND d.fecha BETWEEN :desde AND :hasta
                AND d.track_id > 0
                AND '.$estado.'
            ORDER BY d.fecha DESC, t.tipo, d.folio DESC

        ', $vars);
    }

    /**
     * Método que entrega el detalle de los documentos emitidos que aun no han
     * sido enviado al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getDocumentosEmitidosSinEnviar()
    {
        // forma de obtener razón social
        $razon_social_xpath = $this->db->xml('d.xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/RznSocRecep', 'http://www.sii.cl/SiiDte');
        $razon_social = 'CASE WHEN d.dte NOT IN (110, 111, 112) THEN r.razon_social ELSE '.$razon_social_xpath.' END AS razon_social';
        // realizar consulta
        return $this->db->getTable('
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                '.$razon_social.',
                d.fecha,
                d.total,
                i.glosa AS intercambio,
                d.sucursal_sii,
                u.usuario
            FROM
                dte_emitido AS d LEFT JOIN dte_intercambio_resultado_dte AS i
                    ON i.emisor = d.emisor AND i.dte = d.dte AND i.folio = d.folio AND i.certificacion = d.certificacion,
                dte_tipo AS t,
                contribuyente AS r,
                usuario AS u
            WHERE
                d.dte = t.codigo
                AND d.receptor = r.rut
                AND d.usuario = u.id
                AND d.emisor = :rut
                AND d.certificacion = :certificacion
                AND d.dte NOT IN (39, 41)
                AND d.track_id IS NULL
            ORDER BY d.fecha DESC, t.tipo, d.folio DESC

        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion]);
    }

    /**
     * Método que entrega el resumen de los estados de los DTE para un periodo de tiempo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-23
     */
    public function getDocumentosEmitidosResumenEstadoIntercambio($desde, $hasta)
    {
        return $this->db->getTable('
            SELECT
                CASE WHEN recibo.responde IS NOT NULL THEN true ELSE false END AS recibo,
                recepcion.estado AS recepcion,
                resultado.estado  AS resultado,
                COUNT(*) AS total
            FROM
                dte_emitido AS e
                LEFT JOIN dte_intercambio_recibo_dte AS recibo ON recibo.emisor = e.emisor AND recibo.dte = e.dte AND recibo.folio = e.folio AND recibo.certificacion = e.certificacion
                LEFT JOIN dte_intercambio_recepcion_dte AS recepcion ON recepcion.emisor = e.emisor AND recepcion.dte = e.dte AND recepcion.folio = e.folio AND recepcion.certificacion = e.certificacion
                LEFT JOIN dte_intercambio_resultado_dte AS resultado ON resultado.emisor = e.emisor AND resultado.dte = e.dte AND resultado.folio = e.folio AND resultado.certificacion = e.certificacion
            WHERE
                e.emisor = :rut
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.track_id > 0
                AND  e.revision_estado IS NOT NULL
            GROUP BY recibo, recepcion, resultado
            ORDER BY total DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
    }

    /**
     * Método que entrega los estados de los DTE para un periodo de tiempo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-23
     */
    public function getDocumentosEmitidosEstadoIntercambio($desde, $hasta, $recibo, $recepcion, $resultado)
    {
        // filtros
        $vars = [':rut'=>$this->rut, ':certificacion'=>(int)$this->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta];
        $where = [$recibo ? 'recibo.responde IS NOT NULL' : 'recibo.responde IS NULL'];
        if ($recepcion!==null and $recepcion!=-1) {
            $where[] = 'recepcion.estado = :recepcion';
            $vars[':recepcion'] = $recepcion;
        } else {
            $where[] = 'recepcion.estado IS NULL';
        }
        if ($resultado!==null and $resultado!=-1) {
            $where[] = 'resultado.estado = :resultado';
            $vars[':resultado'] = $resultado;
        } else {
            $where[] = 'resultado.estado IS NULL';
        }
        // forma de obtener razón social
        $razon_social_xpath = $this->db->xml('e.xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/RznSocRecep', 'http://www.sii.cl/SiiDte');
        $razon_social = 'CASE WHEN e.dte NOT IN (110, 111, 112) THEN r.razon_social ELSE '.$razon_social_xpath.' END AS razon_social';
        // realizar consulta
        return $this->db->getTable('
            SELECT
                e.dte,
                t.tipo,
                e.folio,
                '.$razon_social.',
                e.fecha,
                e.total,
                e.revision_estado,
                e.sucursal_sii,
                u.usuario
            FROM
                dte_emitido AS e
                LEFT JOIN dte_intercambio_recibo_dte AS recibo ON recibo.emisor = e.emisor AND recibo.dte = e.dte AND recibo.folio = e.folio AND recibo.certificacion = e.certificacion
                LEFT JOIN dte_intercambio_recepcion_dte AS recepcion ON recepcion.emisor = e.emisor AND recepcion.dte = e.dte AND recepcion.folio = e.folio AND recepcion.certificacion = e.certificacion
                LEFT JOIN dte_intercambio_resultado_dte AS resultado ON resultado.emisor = e.emisor AND resultado.dte = e.dte AND resultado.folio = e.folio AND resultado.certificacion = e.certificacion
                JOIN dte_tipo AS t ON t.codigo = e.dte
                JOIN contribuyente AS r ON r.rut = e.receptor
                JOIN usuario AS u ON u.id = e.usuario
            WHERE
                e.emisor = :rut
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.track_id > 0
                AND  e.revision_estado IS NOT NULL
                AND '.implode(' AND ', $where).'
            ORDER BY e.fecha DESC, t.tipo, e.folio DESC
        ', $vars);
    }

}
