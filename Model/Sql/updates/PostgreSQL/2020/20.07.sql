BEGIN;

--
-- Actualización 20.07
--

--
-- Función que indica si un valor es no un número
--
DROP FUNCTION IF EXISTS is_numeric(v_number TEXT);
CREATE OR REPLACE FUNCTION is_numeric(v_number TEXT)
RETURNS BOOLEAN
AS $$
DECLARE X NUMERIC;
BEGIN
    x = v_number::NUMERIC;
    RETURN TRUE;
EXCEPTION WHEN others THEN
    RETURN FALSE;
END
$$ LANGUAGE plpgsql;

COMMIT;
