--
-- Actualización al día 30 de junio de 2017
--

BEGIN;

ALTER TABLE dte_intercambio_recepcion ALTER glosa DROP NOT NULL;

COMMIT;
