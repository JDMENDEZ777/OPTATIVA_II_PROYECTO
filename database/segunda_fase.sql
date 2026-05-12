-- =========================================================
-- SEGUNDA FASE - SISTEMA LOGIN, ROLES, NOTAS, INASISTENCIAS
-- PostgreSQL / pgAdmin 4
-- Base de datos: sistema_login
-- =========================================================

-- 1. Agregar rol Docente si no existe
INSERT INTO roles (nombre)
VALUES ('Docente')
ON CONFLICT (nombre) DO NOTHING;

-- 2. Ampliar tabla usuarios para bloqueo y auditoria basica
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS email VARCHAR(100);

ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS intentos_fallidos INTEGER NOT NULL DEFAULT 0;

ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS bloqueado BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS bloqueado_en TIMESTAMP NULL;

ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS ultimo_acceso TIMESTAMP NULL;

-- 3. Evitar correos repetidos cuando exista email
-- Si ya existen correos duplicados, se conserva el correo del usuario con menor id
-- y a los demas duplicados se les deja email en NULL para no borrar informacion.
WITH correos_duplicados AS (
    SELECT id,
           ROW_NUMBER() OVER (PARTITION BY LOWER(email) ORDER BY id) AS posicion
    FROM usuarios
    WHERE email IS NOT NULL
)
UPDATE usuarios u
SET email = NULL
FROM correos_duplicados cd
WHERE u.id = cd.id
  AND cd.posicion > 1;

CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_email_unico
ON usuarios (email)
WHERE email IS NOT NULL;

-- 4. Tabla de materias
CREATE TABLE IF NOT EXISTS materias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabla de notas
CREATE TABLE IF NOT EXISTS notas (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    docente_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    materia_id INTEGER NOT NULL REFERENCES materias(id) ON DELETE CASCADE,
    nota NUMERIC(3,2) NOT NULL CHECK (nota >= 0 AND nota <= 5),
    estado VARCHAR(20) NOT NULL DEFAULT 'activa' CHECK (estado IN ('activa', 'bloqueada', 'inactiva')),
    observacion VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL
);

ALTER TABLE notas
ADD COLUMN IF NOT EXISTS estado VARCHAR(20) NOT NULL DEFAULT 'activa';

ALTER TABLE notas
ADD COLUMN IF NOT EXISTS actualizado_en TIMESTAMP NULL;

ALTER TABLE notas
DROP CONSTRAINT IF EXISTS notas_estado_check;

ALTER TABLE notas
ADD CONSTRAINT notas_estado_check CHECK (estado IN ('activa', 'bloqueada', 'inactiva'));

-- 6. Tabla de inasistencias
CREATE TABLE IF NOT EXISTS inasistencias (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    docente_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    materia_id INTEGER NOT NULL REFERENCES materias(id) ON DELETE CASCADE,
    fecha DATE NOT NULL,
    justificada BOOLEAN NOT NULL DEFAULT FALSE,
    observacion VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Tabla de auditoria
CREATE TABLE IF NOT EXISTS auditoria (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    username VARCHAR(50),
    accion VARCHAR(100) NOT NULL,
    detalle VARCHAR(255),
    ip VARCHAR(45),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Tabla de logs especifica para modificaciones de notas
CREATE TABLE IF NOT EXISTS logs_notas (
    id SERIAL PRIMARY KEY,
    nota_id INTEGER NULL REFERENCES notas(id) ON DELETE SET NULL,
    usuario_id INTEGER NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    username VARCHAR(50),
    accion VARCHAR(100) NOT NULL,
    nota_anterior NUMERIC(3,2),
    nota_nueva NUMERIC(3,2),
    estado_anterior VARCHAR(20),
    estado_nuevo VARCHAR(20),
    detalle VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Indices para mejorar consultas frecuentes y autenticacion
CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_notas_estudiante ON notas(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_notas_estado ON notas(estado);
CREATE INDEX IF NOT EXISTS idx_inasistencias_estudiante_fecha ON inasistencias(estudiante_id, fecha);
CREATE INDEX IF NOT EXISTS idx_auditoria_fecha ON auditoria(creado_en);

-- 10. Datos base de materias
INSERT INTO materias (nombre) VALUES
('Pruebas de Software'),
('Ingenieria de Software')
ON CONFLICT (nombre) DO NOTHING;

-- 11. Usuarios de prueba para la segunda fase
-- Las contrasenas existentes se cifran desde PHP al iniciar sesion o al registrar nuevos usuarios.
INSERT INTO usuarios (username, email, password, rol_id)
SELECT 'Profesor', 'profesor@fet.edu.co', 'docente123', r.id
FROM roles r
WHERE r.nombre = 'Docente'
ON CONFLICT (username) DO NOTHING;

UPDATE usuarios
SET email = 'admin@fet.edu.co'
WHERE username = 'admin' AND email IS NULL;

UPDATE usuarios
SET email = 'pepito@fet.edu.co'
WHERE username = 'Pepito' AND email IS NULL;
