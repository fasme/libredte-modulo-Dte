BEGIN;

--
-- Actualización al día 01 de mayo de 2019
--

-- índice para búsqueda de contribuyente por email
CREATE INDEX contribuyente_email_idx ON contribuyente (email);

COMMIT;
