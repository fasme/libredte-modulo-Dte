--
-- Actualización al día 23 de junio de 2017
--

START TRANSACTION;

ALTER TABLE dte_intercambio_recepcion MODIFY glosa VARCHAR(256) NULL;
UPDATE contribuyente_usuario SET permiso = 'dte';

-- fix track_id de INTEGER a BIGINT
ALTER TABLE dte_emitido MODIFY track_id BIGINT;
ALTER TABLE dte_emitido MODIFY cesion_track_id BIGINT;
ALTER TABLE dte_venta MODIFY track_id BIGINT;
ALTER TABLE dte_compra MODIFY track_id BIGINT;
ALTER TABLE dte_guia MODIFY track_id BIGINT;
ALTER TABLE dte_boleta_consumo MODIFY track_id BIGINT;

COMMIT;
