-- DebiHaby Database Schema - VERSIÓN FINAL ACTUALIZADA

CREATE DATABASE IF NOT EXISTS debihaby_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE debihaby_db;

-- ─────────────────────────────────────────
-- TABLA: usuarios
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100),
    avatar VARCHAR(255) DEFAULT 'assets/debi_pet.png',
    edad INT,
    nivel_academico VARCHAR(100),
    nivel_conocimiento ENUM('principiante', 'intermedio_basico', 'intermedio', 'avanzado') DEFAULT 'principiante',
    rol ENUM('estudiante', 'docente', 'admin') DEFAULT 'estudiante',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
-- TABLA: estadisticas
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS estadisticas (
    usuario_id INT PRIMARY KEY,
    puntos INT DEFAULT 0,
    nivel INT DEFAULT 1,
    experiencia INT DEFAULT 0,
    racha INT DEFAULT 0,
    ultima_actividad DATE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
-- TABLA: lecciones
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lecciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria ENUM('Activos', 'Pasivos', 'Capital', 'General', 'Estados Financieros') DEFAULT 'General',
    orden INT DEFAULT 0,
    recompensa_xp INT DEFAULT 100,
    icono VARCHAR(50) DEFAULT 'fa-book'
);

-- ─────────────────────────────────────────
-- TABLA: progreso
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS progreso (
    usuario_id INT,
    leccion_id INT,
    estado ENUM('bloqueada', 'disponible', 'completada') DEFAULT 'bloqueada',
    puntaje INT DEFAULT 0,
    completada_en TIMESTAMP NULL,
    PRIMARY KEY (usuario_id, leccion_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (leccion_id) REFERENCES lecciones(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
-- TABLA: catalogo_cuentas (NUEVO)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS catalogo_cuentas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE COMMENT 'Ej. 101, 201',
    nombre VARCHAR(100) NOT NULL COMMENT 'Ej. Bancos, Proveedores',
    tipo ENUM('Activo', 'Pasivo', 'Capital', 'Resultados') NOT NULL,
    naturaleza ENUM('Deudora', 'Acreedora') NOT NULL
);

-- ─────────────────────────────────────────
-- TABLA: ejercicios (NUEVO)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ejercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL COMMENT 'Usuario creador (rol docente)',
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT COMMENT 'Redacción del caso',
    fecha_creacion DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (docente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
-- TABLA: polizas (NUEVO)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS polizas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ejercicio_id INT NOT NULL,
    estudiante_id INT NOT NULL COMMENT 'Alumno que responde',
    tipo ENUM('Ingreso', 'Egreso', 'Diario') NOT NULL,
    fecha_envio DATE DEFAULT (CURRENT_DATE),
    es_correcto BOOLEAN DEFAULT FALSE COMMENT 'Validación del cuadre',
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
-- TABLA: movimientos_diario (NUEVO)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS movimientos_diario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poliza_id INT NOT NULL,
    cuenta_id INT NOT NULL COMMENT 'Referencia al catálogo',
    cargo DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Monto',
    abono DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Monto',
    FOREIGN KEY (poliza_id) REFERENCES polizas(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_id) REFERENCES catalogo_cuentas(id) ON DELETE RESTRICT
);

-- ─────────────────────────────────────────
-- TABLA: noticias
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    categoria VARCHAR(50),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
-- TABLA: mitos
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS mitos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mito TEXT NOT NULL,
    realidad TEXT NOT NULL,
    explicacion TEXT
);

-- ─────────────────────────────────────────
-- TABLA: preguntas_expertos
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS preguntas_expertos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    pregunta TEXT NOT NULL,
    respuesta TEXT,
    estado ENUM('pendiente', 'respondida') DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ═════════════════════════════════════════
-- DATOS INICIALES
-- ═════════════════════════════════════════

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE progreso;
TRUNCATE TABLE estadisticas;
TRUNCATE TABLE movimientos_diario;
TRUNCATE TABLE polizas;
TRUNCATE TABLE ejercicios;
TRUNCATE TABLE catalogo_cuentas;
TRUNCATE TABLE lecciones;
TRUNCATE TABLE preguntas_expertos;
TRUNCATE TABLE noticias;
TRUNCATE TABLE mitos;
TRUNCATE TABLE usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────
-- USUARIOS (contraseña: 'user' para todos)
-- ─────────────────────────────────────────
INSERT INTO usuarios (id, usuario, correo, contrasena, nombre_completo, rol) VALUES 
(1, 'user',    'user@debihaby.edu',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuario Estudiante',   'estudiante'),
(2, 'admin',   'admin@debihaby.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Debi',   'admin'),
(3, 'berny_m', 'berny@cbtis171.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bernardo Martínez',    'estudiante'),
(4, 'docente', 'docente@debihaby.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Docente Demo',         'docente');

-- ─────────────────────────────────────────
-- ESTADÍSTICAS
-- ─────────────────────────────────────────
INSERT INTO estadisticas (usuario_id, puntos, nivel, experiencia, racha) VALUES 
(1, 0,    1, 0,    0),
(2, 5000, 5, 0,    0),
(3, 3200, 4, 1200, 7),
(4, 100,  1, 0,    0);

-- ─────────────────────────────────────────
-- LECCIONES
-- ─────────────────────────────────────────
INSERT INTO lecciones (titulo, descripcion, categoria, orden, recompensa_xp, icono) VALUES 
('¿Qué es un Activo?',    'Conceptos básicos de los bienes de la empresa.',                                       'Activos',             1, 100, 'fa-coins'),
('Activo Circulante',     'Bienes de alta disponibilidad: efectivo, inventario, cuentas por cobrar.',             'Activos',             2, 120, 'fa-wallet'),
('Activo Fijo',           'Bienes de uso permanente: maquinaria, edificios, vehículos.',                          'Activos',             3, 120, 'fa-building'),
('¿Qué es un Pasivo?',    'Las obligaciones y deudas del negocio con terceros.',                                   'Pasivos',             4, 100, 'fa-file-invoice-dollar'),
('El Capital Contable',   'Patrimonio neto de los dueños: activo menos pasivo.',                                   'Capital',             5, 150, 'fa-vault'),
('La Ecuación Contable',  'El balance fundamental: Activo = Pasivo + Capital.',                                    'General',             6, 130, 'fa-scale-balanced'),
('Ingresos y Gastos',     'Cómo se generan las utilidades y pérdidas de un negocio.',                             'Estados Financieros', 7, 140, 'fa-chart-line'),
('El Balance General',    'El estado financiero más importante: fotografía del negocio.',                          'Estados Financieros', 8, 160, 'fa-table-columns');

-- ─────────────────────────────────────────
-- PROGRESO
-- ─────────────────────────────────────────
INSERT INTO progreso (usuario_id, leccion_id, estado) VALUES 
(1, 1, 'disponible'),
(1, 2, 'bloqueada'),
(1, 3, 'bloqueada'),
(1, 4, 'bloqueada'),
(1, 5, 'bloqueada'),
(1, 6, 'bloqueada'),
(1, 7, 'bloqueada'),
(1, 8, 'bloqueada');

INSERT INTO progreso (usuario_id, leccion_id, estado, puntaje) VALUES 
(3, 1, 'completada', 95),
(3, 2, 'completada', 88),
(3, 3, 'completada', 92),
(3, 4, 'disponible',  0),
(3, 5, 'bloqueada',   0),
(3, 6, 'bloqueada',   0),
(3, 7, 'bloqueada',   0),
(3, 8, 'bloqueada',   0);

-- ─────────────────────────────────────────
-- CATÁLOGO DE CUENTAS (NUEVO)
-- ─────────────────────────────────────────
INSERT INTO catalogo_cuentas (codigo, nombre, tipo, naturaleza) VALUES
('101', 'Caja',                      'Activo',    'Deudora'),
('102', 'Bancos',                    'Activo',    'Deudora'),
('103', 'Cuentas por Cobrar',        'Activo',    'Deudora'),
('104', 'Inventario',                'Activo',    'Deudora'),
('151', 'Mobiliario y Equipo',       'Activo',    'Deudora'),
('152', 'Depreciación Acumulada',    'Activo',    'Acreedora'),
('201', 'Proveedores',               'Pasivo',    'Acreedora'),
('202', 'Acreedores Diversos',       'Pasivo',    'Acreedora'),
('203', 'IVA por Pagar',             'Pasivo',    'Acreedora'),
('301', 'Capital Social',            'Capital',   'Acreedora'),
('302', 'Utilidad del Ejercicio',    'Capital',   'Acreedora'),
('401', 'Ventas',                    'Resultados','Acreedora'),
('501', 'Costo de Ventas',           'Resultados','Deudora'),
('502', 'Gastos de Operación',       'Resultados','Deudora');

-- ─────────────────────────────────────────
-- EJERCICIO DE DEMO (NUEVO)
-- ─────────────────────────────────────────
INSERT INTO ejercicios (id, docente_id, titulo, descripcion) VALUES
(1, 4, 'Compra de mercancía al contado',
 'La empresa "Comercial SA" compra mercancía por $10,000 MXN pagando con cheque del banco. Registra la póliza correspondiente.');

-- ─────────────────────────────────────────
-- PÓLIZA DE DEMO (NUEVO)
-- ─────────────────────────────────────────
INSERT INTO polizas (id, ejercicio_id, estudiante_id, tipo, es_correcto) VALUES
(1, 1, 3, 'Diario', TRUE);

-- ─────────────────────────────────────────
-- MOVIMIENTOS DE DEMO (NUEVO)
-- ─────────────────────────────────────────
INSERT INTO movimientos_diario (poliza_id, cuenta_id, cargo, abono) VALUES
(1, (SELECT id FROM catalogo_cuentas WHERE codigo = '104'), 10000.00, 0.00),
(1, (SELECT id FROM catalogo_cuentas WHERE codigo = '102'), 0.00,     10000.00);

-- ─────────────────────────────────────────
-- NOTICIAS
-- ─────────────────────────────────────────
INSERT INTO noticias (titulo, contenido, categoria) VALUES 
('Nuevas NIF 2026 en México',
 'El CINIF actualizó las Normas de Información Financiera para 2026, con cambios en el reconocimiento de arrendamientos y activos intangibles. Se recomienda revisión de los contratos vigentes.',
 'Fiscal'),
('La contabilidad digital transforma a las PyMES',
 'El uso de software contable en la nube creció un 240% entre las pequeñas empresas mexicanas. Herramientas como Aspel, ContPAQi y SAP Business One lideran el mercado.',
 'Tecnología'),
('DebiHaby: Aprende contabilidad jugando',
 '¡La plataforma educativa gamificada ya está disponible! Desarrollada por estudiantes del CBTis 171, combina lecciones interactivas con retos y recompensas para hacer el aprendizaje contable divertido.',
 'Educación'),
('SAT actualiza requisitos de facturación CFDI 4.0',
 'A partir de 2025, todos los comprobantes fiscales deben emitirse bajo el estándar CFDI 4.0 con nuevos campos obligatorios de exportación y objeto de impuesto.',
 'Fiscal'),
('Inflación y contabilidad: cómo proteger tu patrimonio',
 'Con tasas de inflación elevadas, los activos fijos pueden estar subvalorados en los libros contables. El revalúo de activos y los ajustes por inflación son herramientas esenciales para reflejar la realidad económica.',
 'Economía');

-- ─────────────────────────────────────────
-- MITOS
-- ─────────────────────────────────────────
INSERT INTO mitos (mito, realidad, explicacion) VALUES 
('La contabilidad es solo matemáticas difíciles',
 'Es lógica, orden y pensamiento crítico',
 'La contabilidad usa operaciones básicas: suma, resta, multiplicación y división. Lo que realmente importa es entender los conceptos y el flujo del dinero, no ser un genio matemático.'),
('Solo las grandes empresas necesitan contabilidad',
 'Toda entidad económica la requiere',
 'Desde un pequeño negocio familiar hasta un vendedor ambulante se benefician enormemente de llevar sus cuentas. Las finanzas personales también son contabilidad aplicada a tu vida diaria.'),
('Los contadores solo hacen declaraciones de impuestos',
 'Los contadores son asesores estratégicos clave',
 'Los contadores modernos analizan datos financieros, detectan oportunidades de ahorro, ayudan en la toma de decisiones de inversión y contribuyen directamente al crecimiento de las empresas.'),
('La contabilidad es un trabajo aburrido y repetitivo',
 'Es una carrera dinámica con enorme impacto',
 'Con tecnología como la inteligencia artificial, ERPs y análisis de datos, la contabilidad actual es apasionante: los profesionales resuelven problemas complejos y trabajan en sectores de todo tipo.'),
('Si una empresa tiene muchos activos, siempre es exitosa',
 'El éxito depende de la liquidez y rentabilidad',
 'Una empresa puede tener millones en activos fijos (edificios, maquinaria) pero estar en quiebra si no puede pagar sus deudas del mes. El flujo de efectivo y la rentabilidad son igualmente cruciales.'),
('Las deudas siempre son malas en una empresa',
 'El endeudamiento estratégico es una herramienta de crecimiento',
 'Financiar inversiones mediante deuda puede multiplicar la capacidad productiva de una empresa. Las grandes corporaciones usan estratégicamente el apalancamiento financiero para crecer más rápido que con capital propio.'),
('El balance general muestra cuánto dinero tiene la empresa',
 'Muestra la situación patrimonial total, no solo el efectivo',
 'El Balance General incluye todos los activos (no solo dinero), todas las deudas y el patrimonio de los dueños. Para ver el efectivo específicamente, se revisa el Estado de Flujo de Efectivo.'),
('La contabilidad ya no importa con tantas apps de finanzas',
 'Las apps se basan en principios contables que necesitas entender',
 'Las aplicaciones de finanzas son herramientas, no sustitutos del conocimiento. Para interpretar correctamente los reportes que generan y tomar decisiones inteligentes, necesitas entender los principios contables fundamentales.');

-- ─────────────────────────────────────────
-- PREGUNTAS A EXPERTOS
-- ─────────────────────────────────────────
INSERT INTO preguntas_expertos (usuario_id, pregunta, respuesta, estado) VALUES 
(1, '¿Qué es el IVA?',
 'El Impuesto al Valor Agregado (IVA) es un impuesto indirecto sobre el consumo de bienes y servicios. En México la tasa general es del 16% y del 8% en zona fronteriza. Lo paga el consumidor final pero lo recauda el vendedor para entregarlo al SAT.',
 'respondida'),
(3, '¿Cuándo una empresa debe llevar contabilidad formal?',
 'En México, toda persona moral (empresa constituida) está obligada a llevar contabilidad formal desde su constitución. Las personas físicas con actividad empresarial también, dependiendo de sus ingresos. El SAT requiere que la contabilidad se lleve en sistemas electrónicos aprobados.',
 'respondida'),
(3, '¿Qué diferencia hay entre utilidad bruta y utilidad neta?',
 'La Utilidad Bruta = Ventas − Costo de Ventas. La Utilidad Neta es el resultado final después de restar todos los gastos operativos, financieros e impuestos. La utilidad neta es la ganancia real que queda para los dueños al final del período.',
 'respondida');