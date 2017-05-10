--
-- Actualización al día 30 de junio de 2017
--

BEGIN;

ALTER TABLE dte_intercambio_recepcion ALTER glosa DROP NOT NULL;

-- fix track_id de INTEGER a BIGINT
ALTER TABLE dte_emitido ALTER track_id TYPE BIGINT;
ALTER TABLE dte_emitido ALTER cesion_track_id TYPE BIGINT;
ALTER TABLE dte_venta ALTER track_id TYPE BIGINT;
ALTER TABLE dte_compra ALTER track_id TYPE BIGINT;
ALTER TABLE dte_guia ALTER track_id TYPE BIGINT;
ALTER TABLE dte_boleta_consumo ALTER track_id TYPE BIGINT;

COMMIT;
