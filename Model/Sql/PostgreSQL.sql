BEGIN;

-- tabla para firmas electrónicas
DROP TABLE IF EXISTS firma_electronica CASCADE;
CREATE TABLE firma_electronica (
	run VARCHAR (10) PRIMARY KEY,
	nombre VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL,
	desde TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	hasta TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	emisor VARCHAR(100) NOT NULL,
	usuario INTEGER NOT NULL,
	archivo TEXT NOT NULL,
	contrasenia VARCHAR(255) NOT NULL,
	CONSTRAINT firma_electronica_usuario_fk FOREIGN KEY (usuario)
		REFERENCES usuario (id) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE UNIQUE INDEX firma_electronica_usuario_idx ON firma_electronica (usuario);

-- tipos de documentos (electrónicos y no electrónicos)
DROP TABLE IF EXISTS dte_tipo CASCADE;
CREATE TABLE dte_tipo (
	codigo SMALLINT PRIMARY KEY,
	tipo CHARACTER VARYING (60) NOT NULL,
	electronico BOOLEAN NOT NULL DEFAULT true,
	compra BOOLEAN NOT NULL DEFAULT false,
	venta BOOLEAN NOT NULL DEFAULT false,
	categoria CHAR(1) NOT NULL DEFAULT 'T' CHECK (categoria IN ('T', 'I')),
	enviar BOOLEAN NOT NULL DEFAULT false CHECK ((enviar = true AND electronico = true) OR enviar = false),
	cedible BOOLEAN NOT NULL DEFAULT false CHECK ((categoria = 'T' AND cedible = true) OR cedible = false),
	operacion CHAR(1) CHECK ((categoria = 'T' AND operacion IN ('S', 'R')) OR (categoria != 'T' AND operacion IS NULL))
);
COMMENT ON TABLE dte_tipo IS 'Tipos de documentos (electrónicos y no electrónicos)';
COMMENT ON COLUMN dte_tipo.codigo IS 'Código asignado por el SII al tipo de documento';
COMMENT ON COLUMN dte_tipo.tipo IS 'Nombre del tipo de documento';
COMMENT ON COLUMN dte_tipo.electronico IS 'Indica si el documento es o no electrónico';

-- tabla para iva no recuperable
DROP TABLE IF EXISTS iva_no_recuperable CASCADE;
CREATE TABLE iva_no_recuperable (
	codigo SMALLINT PRIMARY KEY,
	tipo CHARACTER VARYING (70) NOT NULL
);
COMMENT ON TABLE iva_no_recuperable IS 'Tipos de IVA no recuperable';
COMMENT ON COLUMN iva_no_recuperable.codigo IS 'Código asignado por el SII al tipo de IVA';
COMMENT ON COLUMN iva_no_recuperable.tipo IS 'Nombre del tipo de IVA';

-- tabla para impuestos adicionales
DROP TABLE IF EXISTS impuesto_adicional CASCADE;
CREATE TABLE impuesto_adicional (
	codigo SMALLINT PRIMARY KEY,
	retencion_total SMALLINT,
	nombre CHARACTER VARYING (70) NOT NULL,
	tipo CHAR(1),
	tasa REAL,
	descripcion TEXT NOT NULL
);
COMMENT ON TABLE impuesto_adicional IS 'Impuestos adicionales (y retenciones)';
COMMENT ON COLUMN impuesto_adicional.codigo IS 'Código asignado por el SII al impuesto';
COMMENT ON COLUMN impuesto_adicional.retencion_total IS 'Código asignado por el SII al impuesto en caso de ser retención total';
COMMENT ON COLUMN impuesto_adicional.nombre IS 'Nombre del impuesto';
COMMENT ON COLUMN impuesto_adicional.descripcion IS 'Descripción del impuesto (según ley que aplica al mismo)';

-- tabla para tipos de referencia de dte
DROP TABLE IF EXISTS dte_referencia_tipo CASCADE;
CREATE TABLE dte_referencia_tipo (
	codigo SMALLINT PRIMARY KEY,
	tipo VARCHAR(20) NOT NULL
);

-- tabla de contribuyentes
DROP TABLE IF EXISTS contribuyente CASCADE;
CREATE TABLE contribuyente (
	rut INTEGER PRIMARY KEY,
	dv CHAR(1) NOT NULL,
	razon_social VARCHAR(100) NOT NULL,
	giro VARCHAR(80),
	actividad_economica INTEGER,
	telefono VARCHAR(20),
	email VARCHAR (80),
	direccion VARCHAR(70),
	comuna CHAR(5),
	usuario INTEGER,
	modificado TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
	CONSTRAINT contribuyente_actividad_economica_fk FOREIGN KEY (actividad_economica)
		REFERENCES actividad_economica (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT contribuyente_comuna_fk FOREIGN KEY (comuna)
		REFERENCES comuna (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT contribuyente_usuario_fk FOREIGN KEY (usuario)
		REFERENCES usuario (id) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX contribuyente_comuna_idx ON contribuyente (comuna);
CREATE INDEX contribuyente_usuario_idx ON contribuyente (usuario);

-- tabla para los datos extra del contribuyente (email, api, configuraciones, etc)
DROP TABLE IF EXISTS contribuyente_config CASCADE;
CREATE TABLE contribuyente_config (
    contribuyente INTEGER NOT NULL,
    configuracion VARCHAR(32) NOT NULL,
    variable VARCHAR(64) NOT NULL,
    valor TEXT,
    json BOOLEAN NOT NULL DEFAULT false,
    CONSTRAINT contribuyente_config_pkey PRIMARY KEY (contribuyente, configuracion, variable),
    CONSTRAINT contribuyente_config_contribuyente_fk FOREIGN KEY (contribuyente)
                REFERENCES contribuyente (rut) MATCH FULL
                ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla para los DTE que tienen autorizados los contribuyentes en la webapp
DROP TABLE IF EXISTS contribuyente_dte CASCADE;
CREATE TABLE contribuyente_dte (
	contribuyente INTEGER,
	dte SMALLINT,
	activo BOOLEAN NOT NULL DEFAULT true,
	CONSTRAINT contribuyente_dte_pkey PRIMARY KEY (contribuyente, dte),
	CONSTRAINT contribuyente_dte_contribuyente_fk FOREIGN KEY (contribuyente)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT contribuyente_dte_dte_fk FOREIGN KEY (dte)
		REFERENCES dte_tipo (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT
);

-- tabla de usuarios que pueden trabajar con el contribuyente
DROP TABLE IF EXISTS contribuyente_usuario CASCADE;
CREATE TABLE contribuyente_usuario (
	contribuyente INTEGER,
	usuario INTEGER,
	permiso VARCHAR(20),
	CONSTRAINT contribuyente_usuario_pkey PRIMARY KEY (contribuyente, usuario, permiso),
	CONSTRAINT contribuyente_usuario_contribuyente_fk FOREIGN KEY (contribuyente)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT contribuyente_usuario_usuario_fk FOREIGN KEY (usuario)
		REFERENCES usuario (id) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE INDEX contribuyente_usuario_usuario_idx ON contribuyente_usuario (usuario);

-- tabla con los permisos que tiene cada usuario sobre cada tipo de dte que el contribuyente puede emitir
DROP TABLE IF EXISTS contribuyente_usuario_dte CASCADE;
CREATE TABLE contribuyente_usuario_dte (
    contribuyente INTEGER NOT NULL,
    usuario INTEGER NOT NULL,
    dte SMALLINT NOT NULL,
    CONSTRAINT contribuyente_usuario_dte_pk PRIMARY KEY (contribuyente, usuario, dte),
    CONSTRAINT contribuyente_usuario_dte_contribuyente_fk FOREIGN KEY (contribuyente)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT contribuyente_usuario_dte_usuario_fk FOREIGN KEY (usuario)
        REFERENCES usuario (id) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT contribuyente_usuario_dte_dte_fk FOREIGN KEY (dte)
        REFERENCES dte_tipo (codigo) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla para mantedor de folios
DROP TABLE IF EXISTS dte_folio CASCADE;
CREATE TABLE dte_folio (
	emisor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	siguiente INTEGER NOT NULL,
	disponibles INTEGER NOT NULL,
	alerta INTEGER NOT NULL,
	alertado BOOLEAN NOT NULL DEFAULT false,
	CONSTRAINT dte_folio_pk PRIMARY KEY (emisor, dte, certificacion),
	CONSTRAINT dte_folio_emisor_fk FOREIGN KEY (emisor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_folio_dte_fk FOREIGN KEY (dte)
		REFERENCES dte_tipo (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT
);

-- tabla para xml de caf
DROP TABLE IF EXISTS dte_caf CASCADE;
CREATE TABLE dte_caf (
	emisor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	desde INTEGER NOT NULL,
	hasta INTEGER NOT NULL,
	xml TEXT NOT NULL,
	CONSTRAINT dte_caf_pk PRIMARY KEY (emisor, dte, certificacion, desde),
	CONSTRAINT dte_caf_emisor_dte_certificacion_fk FOREIGN KEY (emisor, dte, certificacion)
		REFERENCES dte_folio (emisor, dte, certificacion) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla para dte temporales
DROP TABLE IF EXISTS dte_tmp CASCADE;
CREATE TABLE dte_tmp (
	emisor INTEGER NOT NULL,
	receptor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	codigo CHAR(32) NOT NULL,
	fecha DATE NOT NULL,
	total INTEGER NOT NULL,
	datos TEXT NOT NULL,
	sucursal_sii INTEGER,
	usuario INTEGER NOT NULL,
	CONSTRAINT dte_tmp_pkey PRIMARY KEY (emisor, receptor, dte, codigo),
	CONSTRAINT dte_tmp_emisor_fk FOREIGN KEY (emisor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_tmp_receptor_fk FOREIGN KEY (receptor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_tmp_dte_fk FOREIGN KEY (dte)
		REFERENCES dte_tipo (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT dte_tmp_usuario_fk FOREIGN KEY (usuario)
		REFERENCES usuario (id) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX dte_tmp_sucursal_sii_idx ON dte_tmp (sucursal_sii);
CREATE INDEX dte_tmp_usuario_idx ON dte_tmp (usuario);

-- tabla para dte emitido
DROP TABLE IF EXISTS dte_emitido CASCADE;
CREATE TABLE dte_emitido (
	emisor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	folio INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	tasa SMALLINT NOT NULL DEFAULT 0,
	fecha DATE NOT NULL,
	sucursal_sii INTEGER,
	receptor INTEGER NOT NULL,
	exento INTEGER,
	neto INTEGER,
	iva INTEGER NOT NULL DEFAULT 0,
	total INTEGER NOT NULL,
	usuario INTEGER NOT NULL,
	xml TEXT NOT NULL,
	track_id BIGINT,
	revision_estado VARCHAR(100),
	revision_detalle TEXT,
	anulado BOOLEAN NOT NULL DEFAULT false,
	iva_fuera_plazo BOOLEAN NOT NULL DEFAULT false,
	cesion_xml TEXT,
	cesion_track_id BIGINT,
	receptor_evento CHAR(1),
	CONSTRAINT dte_emitido_pk PRIMARY KEY (emisor, dte, folio, certificacion),
	CONSTRAINT dte_emitido_emisor_fk FOREIGN KEY (emisor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_emitido_dte_fk FOREIGN KEY (dte)
		REFERENCES dte_tipo (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT dte_emitido_receptor_fk FOREIGN KEY (receptor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT dte_emitido_usuario_fk FOREIGN KEY (usuario)
		REFERENCES usuario (id) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX dte_emitido_fecha_emisor_idx ON dte_emitido (fecha, emisor);
CREATE INDEX dte_emitido_receptor_emisor_idx ON dte_emitido (receptor, emisor);
CREATE INDEX dte_emitido_usuario_emisor_idx ON dte_emitido (usuario, emisor);

-- tabla para los correos de los DTE emitidos
DROP TABLE IF EXISTS dte_emitido_email CASCADE;
CREATE TABLE dte_emitido_email (
    emisor INTEGER NOT NULL,
    dte SMALLINT NOT NULL,
    folio INTEGER NOT NULL,
    certificacion BOOLEAN NOT NULL DEFAULT false,
    email VARCHAR(80) NOT NULL,
    fecha_hora TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    CONSTRAINT dte_emitido_email_pk PRIMARY KEY (emisor, dte, folio, certificacion, email, fecha_hora),
    CONSTRAINT dte_emitido_email_dte_emitido_fk FOREIGN KEY (emisor, dte, folio, certificacion)
        REFERENCES dte_emitido (emisor, dte, folio, certificacion) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla para referencias de los dte
DROP TABLE IF EXISTS dte_referencia CASCADE;
CREATE TABLE dte_referencia (
	emisor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	folio INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	referencia_dte SMALLINT NOT NULL,
	referencia_folio INTEGER NOT NULL,
	codigo SMALLINT,
	razon VARCHAR(90),
	CONSTRAINT dte_referencia_pk PRIMARY KEY (emisor, dte, folio, certificacion, referencia_dte, referencia_folio),
	CONSTRAINT dte_referencia_emisor_fk FOREIGN KEY (emisor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_referencia_dte_fk FOREIGN KEY (dte)
		REFERENCES dte_tipo (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT dte_referencia_referencia_dte_fk FOREIGN KEY (referencia_dte)
		REFERENCES dte_tipo (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT dte_referencia_codigo_fk FOREIGN KEY (codigo)
		REFERENCES dte_referencia_tipo (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX dte_referencia_dte_folio_idx ON dte_referencia (referencia_dte, referencia_folio);

-- tabla para libro de ventas envíados
DROP TABLE IF EXISTS dte_venta CASCADE;
CREATE TABLE dte_venta (
	emisor INTEGER NOT NULL,
	periodo INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	documentos INTEGER NOT NULL,
	xml TEXT NOT NULL,
	track_id BIGINT,
	revision_estado VARCHAR(100),
	revision_detalle TEXT,
	CONSTRAINT dte_venta_pk PRIMARY KEY (emisor, periodo, certificacion),
	CONSTRAINT dte_venta_emisor_fk FOREIGN KEY (emisor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla para intercambio de contribuyentes
DROP TABLE IF EXISTS dte_intercambio CASCADE;
CREATE TABLE dte_intercambio (
	receptor INTEGER NOT NULL,
	codigo INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	fecha_hora_email TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	asunto VARCHAR(100) NOT NULL,
	de VARCHAR(80) NOT NULL,
	responder_a VARCHAR(80),
	mensaje TEXT,
	mensaje_html TEXT,
	emisor INTEGER NOT NULL,
	fecha_hora_firma TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	documentos SMALLINT NOT NULL,
	archivo VARCHAR(100) NOT NULL,
	archivo_xml TEXT NOT NULL,
	archivo_md5 CHAR(32) NOT NULL,
	fecha_hora_respuesta TIMESTAMP WITHOUT TIME ZONE,
	estado SMALLINT,
	recepcion_xml TEXT,
	recibos_xml TEXT,
	resultado_xml TEXT,
	usuario INTEGER,
	CONSTRAINT dte_intercambio_pk PRIMARY KEY (receptor, codigo, certificacion),
	CONSTRAINT dte_intercambio_receptor_fk FOREIGN KEY (receptor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_intercambio_usuario_fk FOREIGN KEY (usuario)
		REFERENCES usuario (id) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE UNIQUE INDEX dte_intercambio_unique_idx ON dte_intercambio (receptor, certificacion, fecha_hora_firma, archivo_md5);

-- tabla para dte recibido
DROP TABLE IF EXISTS dte_recibido CASCADE;
CREATE TABLE dte_recibido (
	emisor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	folio BIGINT NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	receptor INTEGER NOT NULL,
	tasa REAL NOT NULL DEFAULT 0,
	fecha DATE NOT NULL,
	sucursal_sii INTEGER,
	exento INTEGER,
	neto INTEGER,
	iva INTEGER NOT NULL DEFAULT 0,
	total INTEGER NOT NULL,
	usuario INTEGER NOT NULL,
	intercambio INTEGER,
	iva_uso_comun INTEGER,
	iva_no_recuperable TEXT,
	impuesto_adicional TEXT,
	impuesto_tipo SMALLINT NOT NULL DEFAULT 1,
	anulado CHAR(1),
	impuesto_sin_credito INTEGER,
	monto_activo_fijo INTEGER,
	monto_iva_activo_fijo INTEGER,
	iva_no_retenido INTEGER,
	impuesto_puros INTEGER,
	impuesto_cigarrillos INTEGER,
	impuesto_tabaco_elaborado INTEGER,
	impuesto_vehiculos INTEGER,
	numero_interno INTEGER,
	emisor_nc_nd_fc SMALLINT,
	periodo INTEGER,
	sucursal_sii_receptor INTEGER,
	rcv_accion CHAR(3),
	tipo_transaccion SMALLINT,
	CONSTRAINT dte_recibido_pk PRIMARY KEY (emisor, dte, folio, certificacion),
	CONSTRAINT dte_recibido_emisor_fk FOREIGN KEY (emisor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT dte_recibido_dte_fk FOREIGN KEY (dte)
		REFERENCES dte_tipo (codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT dte_recibido_receptor_fk FOREIGN KEY (receptor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_recibido_usuario_fk FOREIGN KEY (usuario)
		REFERENCES usuario (id) MATCH FULL
		ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT dte_recibido_intercambio_fk FOREIGN KEY (receptor, intercambio, certificacion)
		REFERENCES dte_intercambio (receptor, codigo, certificacion)
		ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX dte_recibido_fecha_emisor_idx ON dte_recibido (fecha, emisor);
CREATE INDEX dte_recibido_receptor_emisor_idx ON dte_recibido (receptor, emisor);

-- tabla para libro de compras envíados al sii
DROP TABLE IF EXISTS dte_compra CASCADE;
CREATE TABLE dte_compra (
	receptor INTEGER NOT NULL,
	periodo INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	documentos INTEGER NOT NULL,
	xml TEXT NOT NULL,
	track_id BIGINT,
	revision_estado VARCHAR(100),
	revision_detalle TEXT,
	CONSTRAINT dte_compra_pk PRIMARY KEY (receptor, periodo, certificacion),
	CONSTRAINT dte_compra_receptor_fk FOREIGN KEY (receptor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);

-- intercambio: recibos
DROP TABLE IF EXISTS dte_intercambio_recibo CASCADE;
CREATE TABLE dte_intercambio_recibo (
	responde INTEGER NOT NULL,
	recibe INTEGER NOT NULL,
	codigo CHAR(32) NOT NULL,
	contacto VARCHAR(40),
	telefono VARCHAR(40),
	email VARCHAR(80),
	fecha_hora TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	xml TEXT NOT NULL,
	CONSTRAINT dte_intercambio_recibo_pk PRIMARY KEY (responde, recibe, codigo),
	CONSTRAINT dte_intercambio_recibo_recibe_fk FOREIGN KEY (recibe)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);
DROP TABLE IF EXISTS dte_intercambio_recibo_dte CASCADE;
CREATE TABLE dte_intercambio_recibo_dte (
	emisor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	folio INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL,
	responde INTEGER NOT NULL,
	codigo CHAR(32) NOT NULL,
	recinto VARCHAR(80) NOT NULL,
	firma VARCHAR(10) NOT NULL,
	fecha_hora TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	CONSTRAINT dte_intercambio_recibo_dte_pk PRIMARY KEY (emisor, dte, folio, certificacion),
	CONSTRAINT dte_intercambio_recibo_dte_pk_fk FOREIGN KEY (emisor, dte, folio, certificacion)
		REFERENCES dte_emitido (emisor, dte, folio, certificacion) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_intercambio_recibo_dte_recibo_fk FOREIGN KEY (responde, emisor, codigo)
		REFERENCES dte_intercambio_recibo (responde, recibe, codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);

-- intercambio: recepcion envio
DROP TABLE IF EXISTS dte_intercambio_recepcion CASCADE;
CREATE TABLE dte_intercambio_recepcion (
	responde INTEGER NOT NULL,
	recibe INTEGER NOT NULL,
	codigo CHAR(32) NOT NULL,
	contacto VARCHAR(40),
	telefono VARCHAR(40),
	email VARCHAR(80),
	fecha_hora TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	estado INTEGER NOT NULL,
	glosa VARCHAR(256),
	xml TEXT NOT NULL,
	CONSTRAINT dte_intercambio_recepcion_pk PRIMARY KEY (responde, recibe, codigo),
	CONSTRAINT dte_intercambio_recepcion_recibe_fk FOREIGN KEY (recibe)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);
DROP TABLE IF EXISTS dte_intercambio_recepcion_dte CASCADE;
CREATE TABLE dte_intercambio_recepcion_dte (
	emisor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	folio INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL,
	responde INTEGER NOT NULL,
	codigo CHAR(32) NOT NULL,
	estado INTEGER NOT NULL,
	glosa VARCHAR(256) NOT NULL,
	CONSTRAINT dte_intercambio_recepcion_dte_pk PRIMARY KEY (emisor, dte, folio, certificacion),
	CONSTRAINT dte_intercambio_recepcion_dte_pk_fk FOREIGN KEY (emisor, dte, folio, certificacion)
		REFERENCES dte_emitido (emisor, dte, folio, certificacion) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_intercambio_recepcion_dte_recepcion_fk FOREIGN KEY (responde, emisor, codigo)
		REFERENCES dte_intercambio_recepcion (responde, recibe, codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);

-- intercambio: resultado dte
DROP TABLE IF EXISTS dte_intercambio_resultado CASCADE;
CREATE TABLE dte_intercambio_resultado (
	responde INTEGER NOT NULL,
	recibe INTEGER NOT NULL,
	codigo CHAR(32) NOT NULL,
	contacto VARCHAR(40),
	telefono VARCHAR(40),
	email VARCHAR(80),
	fecha_hora TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	xml TEXT NOT NULL,
	CONSTRAINT dte_intercambio_resultado_pk PRIMARY KEY (responde, recibe, codigo),
	CONSTRAINT dte_intercambio_resultado_recibe_fk FOREIGN KEY (recibe)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);
DROP TABLE IF EXISTS dte_intercambio_resultado_dte CASCADE;
CREATE TABLE dte_intercambio_resultado_dte (
	emisor INTEGER NOT NULL,
	dte SMALLINT NOT NULL,
	folio INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL,
	responde INTEGER NOT NULL,
	codigo CHAR(32) NOT NULL,
	estado INTEGER NOT NULL,
	glosa VARCHAR(256),
	CONSTRAINT dte_intercambio_resultado_dte_pk PRIMARY KEY (emisor, dte, folio, certificacion),
	CONSTRAINT dte_intercambio_resultado_dte_pk_fk FOREIGN KEY (emisor, dte, folio, certificacion)
		REFERENCES dte_emitido (emisor, dte, folio, certificacion) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT dte_intercambio_resultado_dte_recibo_fk FOREIGN KEY (responde, emisor, codigo)
		REFERENCES dte_intercambio_resultado (responde, recibe, codigo) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla para libro de guías de despacho
DROP TABLE IF EXISTS dte_guia CASCADE;
CREATE TABLE dte_guia (
	emisor INTEGER NOT NULL,
	periodo INTEGER NOT NULL,
	certificacion BOOLEAN NOT NULL DEFAULT false,
	documentos INTEGER NOT NULL,
	xml TEXT NOT NULL,
	track_id BIGINT,
	revision_estado VARCHAR(100),
	revision_detalle TEXT,
	CONSTRAINT dte_guia_pk PRIMARY KEY (emisor, periodo, certificacion),
	CONSTRAINT dte_guia_emisor_fk FOREIGN KEY (emisor)
		REFERENCES contribuyente (rut) MATCH FULL
		ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla para consumo de folios de boletas
DROP TABLE IF EXISTS dte_boleta_consumo CASCADE;
CREATE TABLE dte_boleta_consumo (
        emisor INTEGER NOT NULL,
        dia DATE NOT NULL,
        certificacion BOOLEAN NOT NULL DEFAULT false,
        secuencia INTEGER NOT NULL,
        xml TEXT NOT NULL,
        track_id BIGINT,
        revision_estado VARCHAR(100),
        revision_detalle TEXT,
        CONSTRAINT dte_boleta_consumo_pk PRIMARY KEY (emisor, dia, certificacion),
        CONSTRAINT dte_boleta_consumo_emisor_fk FOREIGN KEY (emisor)
                REFERENCES contribuyente (rut) MATCH FULL
                ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla de clasificaciones de items
DROP TABLE IF EXISTS item_clasificacion CASCADE;
CREATE TABLE item_clasificacion (
    contribuyente INTEGER NOT NULL,
    codigo VARCHAR(35) NOT NULL,
    clasificacion VARCHAR (50) NOT NULL,
    superior VARCHAR(35),
    activa BOOLEAN NOT NULL DEFAULT true,
    CONSTRAINT item_clasificacion_pk PRIMARY KEY (contribuyente, codigo),
    CONSTRAINT item_clasificacion_contribuyente_fk FOREIGN KEY (contribuyente)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT item_clasificacion_contribuyente_superior_fk FOREIGN KEY (contribuyente, superior)
        REFERENCES item_clasificacion (contribuyente, codigo)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- tabla para items comercializados
DROP TABLE IF EXISTS item CASCADE;
CREATE TABLE item (
    contribuyente INTEGER NOT NULL,
    codigo_tipo VARCHAR(10) NOT NULL DEFAULT 'INT1',
    codigo VARCHAR(35) NOT NULL,
    item VARCHAR(80) NOT NULL,
    descripcion VARCHAR(1000),
    clasificacion VARCHAR(10) NOT NULL,
    unidad VARCHAR(4),
    precio REAL NOT NULL CHECK (precio > 0),
    bruto BOOLEAN NOT NULL DEFAULT false,
    moneda VARCHAR(3) NOT NULL,
    exento SMALLINT NOT NULL DEFAULT 0 CHECK (exento >= 0 AND exento <= 6),
    descuento REAL NOT NULL DEFAULT 0 CHECK (descuento >= 0),
    descuento_tipo CHAR(1) NOT NULL DEFAULT '%' CHECK (descuento_tipo IN ('%', '$')),
    impuesto_adicional SMALLINT,
    activo BOOLEAN NOT NULL DEFAULT true,
    CONSTRAINT item_pk PRIMARY KEY (contribuyente, codigo_tipo, codigo),
    CONSTRAINT item_contribuyente_fk FOREIGN KEY (contribuyente)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT item_contribuyente_clasificacion_fk FOREIGN KEY (contribuyente, clasificacion)
        REFERENCES item_clasificacion (contribuyente, codigo) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT item_impuesto_adicional_fk FOREIGN KEY (impuesto_adicional)
        REFERENCES impuesto_adicional (codigo) MATCH FULL
        ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX item_contribuyente_codigo_idx ON item (contribuyente, codigo);

-- tabla para cobranza de dte emitidos con crédito (tienen pagos programados)
DROP TABLE IF EXISTS cobranza CASCADE;
CREATE TABLE cobranza (
    emisor INTEGER NOT NULL,
    dte SMALLINT NOT NULL,
    folio INTEGER NOT NULL,
    certificacion BOOLEAN NOT NULL DEFAULT false,
    fecha DATE NOT NULL,
    monto INTEGER NOT NULL,
    glosa VARCHAR(40),
    pagado INTEGER,
    observacion TEXT,
    usuario INTEGER,
    modificado DATE,
    CONSTRAINT cobranza_pk PRIMARY KEY (emisor, dte, folio, certificacion, fecha),
    CONSTRAINT cobranza_dte_emitido_fk FOREIGN KEY (emisor, dte, folio, certificacion)
        REFERENCES dte_emitido (emisor, dte, folio, certificacion) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT cobranza_usuario_fk FOREIGN KEY (usuario)
        REFERENCES usuario (id) MATCH FULL
        ON UPDATE CASCADE ON DELETE RESTRICT

);
CREATE INDEX cobranza_emisor_certificacion_fecha_idx ON cobranza (emisor, certificacion, fecha);

COMMIT;
