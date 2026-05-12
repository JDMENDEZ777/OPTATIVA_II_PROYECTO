-- =========================================================
-- CREAR ADMINISTRADOR MANUALMENTE DESDE PGADMIN 4
-- Base de datos: sistema_login
-- =========================================================

-- Asegura que exista el rol Administrador
INSERT INTO roles (nombre)
VALUES ('Administrador')
ON CONFLICT (nombre) DO NOTHING;

-- Evita conflicto si otro usuario tenia este correo.
UPDATE usuarios
SET email = NULL
WHERE email = 'adminnuevo@fet.edu.co'
  AND username <> 'adminnuevo';

-- Crea o actualiza un administrador de prueba con contrasena cifrada.
INSERT INTO usuarios (username, email, password, rol_id)
SELECT
    'adminnuevo',
    'adminnuevo@fet.edu.co',
    '$2y$10$QSMnB.sLWdhkNcRiTiaCQ.y599Wjggsxkq0VkPQS2gOyZdxJdbKM.',
    r.id
FROM roles r
WHERE r.nombre = 'Administrador'
ON CONFLICT (username) DO UPDATE
SET email = EXCLUDED.email,
    password = EXCLUDED.password,
    rol_id = EXCLUDED.rol_id,
    bloqueado = false,
    intentos_fallidos = 0,
    bloqueado_en = NULL;

-- Credenciales:
-- Usuario: adminnuevo
-- Contrasena: Admin123

-- Verificacion:
SELECT u.id, u.username, u.email, r.nombre AS rol, u.bloqueado, u.intentos_fallidos
FROM usuarios u
INNER JOIN roles r ON u.rol_id = r.id
WHERE u.username = 'adminnuevo';
