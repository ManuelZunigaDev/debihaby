-- DebiHaby Database Schema - FINAL VERSION WITH AUTH FIXES

CREATE DATABASE IF NOT EXISTS debihaby_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE debihaby_db;

-- Table for users (students)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255) DEFAULT 'assets/debi_pet.png',
    age INT,
    academic_level VARCHAR(100),
    knowledge_level ENUM('principiante', 'intermedio_basico', 'intermedio', 'avanzado') DEFAULT 'principiante',
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for student stats and gamification
CREATE TABLE IF NOT EXISTS user_stats (
    user_id INT PRIMARY KEY,
    points INT DEFAULT 0,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    streak INT DEFAULT 0,
    last_activity DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for lessons/topics
CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('Activos', 'Pasivos', 'Capital', 'General', 'Estados Financieros') DEFAULT 'General',
    order_index INT DEFAULT 0,
    xp_reward INT DEFAULT 100,
    icon_class VARCHAR(50) DEFAULT 'fa-book'
);

-- Table for user progress in lessons
CREATE TABLE IF NOT EXISTS user_progress (
    user_id INT,
    lesson_id INT,
    status ENUM('locked', 'available', 'completed') DEFAULT 'locked',
    score INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    PRIMARY KEY (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- --- INITIAL DATA ---

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE user_progress;
TRUNCATE TABLE user_stats;
TRUNCATE TABLE lessons;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Default User: user / user (Student)
-- PASSWORD_HASH for 'user' is: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (id, username, email, password, full_name, role) VALUES 
(1, 'user', 'user@debihaby.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuario Estudiante', 'student'),
(2, 'admin', 'admin@debihaby.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Debi', 'admin'),
(3, 'berny_m', 'berny@cbtis171.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bernardo Martínez', 'student');

-- Stats
INSERT INTO user_stats (user_id, points, level, experience, streak) VALUES 
(1, 0, 1, 0, 0),
(2, 5000, 5, 0, 0),
(3, 3200, 4, 1200, 7);

-- Lessons (5 originales + 3 nuevas)
INSERT INTO lessons (title, description, category, order_index, xp_reward, icon_class) VALUES 
('¿Qué es un Activo?', 'Conceptos básicos de los bienes de la empresa.', 'Activos', 1, 100, 'fa-coins'),
('Activo Circulante', 'Bienes de alta disponibilidad: efectivo, inventario, cuentas por cobrar.', 'Activos', 2, 120, 'fa-wallet'),
('Activo Fijo', 'Bienes de uso permanente: maquinaria, edificios, vehículos.', 'Activos', 3, 120, 'fa-building'),
('¿Qué es un Pasivo?', 'Las obligaciones y deudas del negocio con terceros.', 'Pasivos', 4, 100, 'fa-file-invoice-dollar'),
('El Capital Contable', 'Patrimonio neto de los dueños: activo menos pasivo.', 'Capital', 5, 150, 'fa-vault'),
('La Ecuación Contable', 'El balance fundamental: Activo = Pasivo + Capital.', 'General', 6, 130, 'fa-scale-balanced'),
('Ingresos y Gastos', 'Cómo se generan las utilidades y pérdidas de un negocio.', 'Estados Financieros', 7, 140, 'fa-chart-line'),
('El Balance General', 'El estado financiero más importante: fotografía del negocio.', 'Estados Financieros', 8, 160, 'fa-table-columns');

-- Initial Progress for Test User (usuario ve la primera lección disponible)
INSERT INTO user_progress (user_id, lesson_id, status) VALUES 
(1, 1, 'available'),
(1, 2, 'locked'),
(1, 3, 'locked'),
(1, 4, 'locked'),
(1, 5, 'locked'),
(1, 6, 'locked'),
(1, 7, 'locked'),
(1, 8, 'locked');

-- Initial Progress for berny_m (most completed to show ranking)
INSERT INTO user_progress (user_id, lesson_id, status, score) VALUES 
(3, 1, 'completed', 95),
(3, 2, 'completed', 88),
(3, 3, 'completed', 92),
(3, 4, 'available', 0),
(3, 5, 'locked', 0),
(3, 6, 'locked', 0),
(3, 7, 'locked', 0),
(3, 8, 'locked', 0);

-- Additional tables for Expert Panel, News and Myths
CREATE TABLE IF NOT EXISTS expert_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    question TEXT NOT NULL,
    answer TEXT,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS myths (
    id INT AUTO_INCREMENT PRIMARY KEY,
    myth TEXT NOT NULL,
    reality TEXT NOT NULL,
    explanation TEXT
);

-- 8 mitos bien documentados
INSERT INTO myths (myth, reality, explanation) VALUES 
('La contabilidad es solo matemáticas difíciles', 'Es lógica, orden y pensamiento crítico', 'La contabilidad usa operaciones básicas: suma, resta, multiplicación y división. Lo que realmente importa es entender los conceptos y el flujo del dinero, no ser un genio matemático.'),
('Solo las grandes empresas necesitan contabilidad', 'Toda entidad económica la requiere', 'Desde un pequeño negocio familiar hasta un vendedor ambulante se benefician enormemente de llevar sus cuentas. Las finanzas personales también son contabilidad aplicada a tu vida diaria.'),
('Los contadores solo hacen declaraciones de impuestos', 'Los contadores son asesores estratégicos clave', 'Los contadores modernos analizan datos financieros, detectan oportunidades de ahorro, ayudan en la toma de decisiones de inversión y contribuyen directamente al crecimiento de las empresas.'),
('La contabilidad es un trabajo aburrido y repetitivo', 'Es una carrera dinámica con enorme impacto', 'Con tecnología como la inteligencia artificial, ERPs y análisis de datos, la contabilidad actual es apasionante: los profesionales resuelven problemas complejos y trabajan en sectores de todo tipo.'),
('Si una empresa tiene muchos activos, siempre es exitosa', 'El éxito depende de la liquidez y rentabilidad', 'Una empresa puede tener millones en activos fijos (edificios, maquinaria) pero estar en quiebra si no puede pagar sus deudas del mes. El flujo de efectivo y la rentabilidad son igualmente cruciales.'),
('Las deudas siempre son malas en una empresa', 'El endeudamiento estratégico es una herramienta de crecimiento', 'Financiar inversiones mediante deuda puede multiplicar la capacidad productiva de una empresa. Las grandes corporaciones usan estratégicamente el apalancamiento financiero para crecer más rápido que con capital propio.'),
('El balance general muestra cuánto dinero tiene la empresa', 'Muestra la situación patrimonial total, no solo el efectivo', 'El Balance General incluye todos los activos (no solo dinero), todas las deudas y el patrimonio de los dueños. Para ver el efectivo específicamente, se revisa el Estado de Flujo de Efectivo.'),
('La contabilidad ya no importa con tantas apps de finanzas', 'Las apps se basan en principios contables que necesitas entender', 'Las aplicaciones de finanzas son herramientas, no sustitutos del conocimiento. Para interpretar correctamente los reportes que generan y tomar decisiones inteligentes, necesitas entender los principios contables fundamentales.');

INSERT INTO news (title, content, category) VALUES 
('Nuevas NIF 2026 en México', 'El CINIF actualizó las Normas de Información Financiera para 2026, con cambios en el reconocimiento de arrendamientos y activos intangibles. Se recomienda revisión de los contratos vigentes.', 'Fiscal'),
('La contabilidad digital transforma a las PyMES', 'El uso de software contable en la nube creció un 240% entre las pequeñas empresas mexicanas. Herramientas como Aspel, ContPAQi y SAP Business One lideran el mercado.', 'Tecnología'),
('DebiHaby: Aprende contabilidad jugando', '¡La plataforma educativa gamificada ya está disponible! Desarrollada por estudiantes del CBTis 171, combina lecciones interactivas con retos y recompensas para hacer el aprendizaje contable divertido.', 'Educación'),
('SAT actualiza requisitos de facturación CFDI 4.0', 'A partir de 2025, todos los comprobantes fiscales deben emitirse bajo el estándar CFDI 4.0 con nuevos campos obligatorios de exportación y objeto de impuesto.', 'Fiscal'),
('Inflación y contabilidad: cómo proteger tu patrimonio', 'Con tasas de inflación elevadas, los activos fijos pueden estar subvalorados en los libros contables. El revalúo de activos y los ajustes por inflación son herramientas esenciales para reflejar la realidad económica.', 'Economía');

INSERT INTO expert_questions (user_id, question, answer, status) VALUES 
(1, '¿Qué es el IVA?', 'El Impuesto al Valor Agregado (IVA) es un impuesto indirecto sobre el consumo de bienes y servicios. En México la tasa general es del 16% y del 8% en zona fronteriza. Lo paga el consumidor final pero lo recauda el vendedor para entregarlo al SAT.', 'answered'),
(3, '¿Cuándo una empresa debe llevar contabilidad formal?', 'En México, toda persona moral (empresa constituida) está obligada a llevar contabilidad formal desde su constitución. Las personas físicas con actividad empresarial también, dependiendo de sus ingresos. El SAT requiere que la contabilidad se lleve en sistemas electrónicos aprobados.', 'answered'),
(3, '¿Qué diferencia hay entre utilidad bruta y utilidad neta?', 'La Utilidad Bruta = Ventas − Costo de Ventas. La Utilidad Neta es el resultado final después de restar todos los gastos operativos, financieros e impuestos. La utilidad neta es la ganancia real que queda para los dueños al final del período.', 'answered');

