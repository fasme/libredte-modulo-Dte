BEGIN;

--
-- Actualización al día 16 de julio de 2019
--

-- índice para búsqueda de contribuyente por email
CREATE INDEX contribuyente_email_idx ON contribuyente (email);

-- tabla para los correos de los DTE temporales
CREATE TABLE dte_tmp_email (
    emisor INTEGER NOT NULL,
    receptor INTEGER NOT NULL,
    dte SMALLINT NOT NULL,
    codigo CHAR(32) NOT NULL,
    email VARCHAR(80) NOT NULL,
    fecha_hora TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    CONSTRAINT dte_tmp_email_pk PRIMARY KEY (emisor, receptor, dte, codigo, email, fecha_hora),
    CONSTRAINT dte_tmp_email_dte_tmp_fk FOREIGN KEY (emisor, receptor, dte, codigo)
        REFERENCES dte_tmp (emisor, receptor, dte, codigo) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);

UPDATE contribuyente_config SET configuracion = 'apps' WHERE configuracion = 'respaldos' AND variable = 'dropbox';

COMMIT;
