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

-- Lessons (Expanded to 15 lessons)
INSERT INTO lessons (title, description, category, order_index, xp_reward, icon_class) VALUES 
('¿Qué es un Activo?', 'Conceptos básicos de los bienes de la empresa.', 'Activos', 1, 100, 'fa-coins'),
('Activo Circulante', 'Bienes de alta disponibilidad: efectivo, inventario, cuentas por cobrar.', 'Activos', 2, 120, 'fa-wallet'),
('Activo Fijo', 'Bienes de uso permanente: maquinaria, edificios, vehículos.', 'Activos', 3, 120, 'fa-building'),
('Activo Diferido', 'Gastos pagados por anticipado que generan beneficios futuros.', 'Activos', 4, 120, 'fa-clock-rotate-left'),
('¿Qué es un Pasivo?', 'Las obligaciones y deudas del negocio con terceros.', 'Pasivos', 5, 100, 'fa-file-invoice-dollar'),
('Pasivo a Corto Plazo', 'Deudas que deben pagarse en menos de un año.', 'Pasivos', 6, 120, 'fa-calendar-day'),
('Pasivo a Largo Plazo', 'Obligaciones con vencimiento mayor a un año.', 'Pasivos', 7, 120, 'fa-calendar-check'),
('El Capital Contable', 'Patrimonio neto de los dueños: activo menos pasivo.', 'Capital', 8, 150, 'fa-vault'),
('Capital Social', 'Aportaciones iniciales y adicionales de los socios.', 'Capital', 9, 130, 'fa-users-gear'),
('La Ecuación Contable', 'El balance fundamental: Activo = Pasivo + Capital.', 'General', 10, 130, 'fa-scale-balanced'),
('Partida Doble', 'El principio de que a todo cargo corresponde un abono.', 'General', 11, 140, 'fa-plus-minus'),
('Ingresos y Gastos', 'Cómo se generan las utilidades y pérdidas de un negocio.', 'Estados Financieros', 12, 140, 'fa-chart-line'),
('El Balance General', 'El estado financiero más importante: fotografía del negocio.', 'Estados Financieros', 13, 160, 'fa-table-columns'),
('Estado de Resultados', 'Muestra la utilidad o pérdida neta de un periodo.', 'Estados Financieros', 14, 160, 'fa-file-lines'),
('Cierre Contable', 'Proceso de finalización de un ciclo contable.', 'General', 15, 200, 'fa-flag-checkered');

-- ... (Progress logic would update accordingly when a user starts)

-- Myths (Expanded)
INSERT INTO myths (myth, reality, explanation) VALUES 
('La contabilidad es solo matemáticas difíciles', 'Es lógica, orden y pensamiento crítico', 'La contabilidad usa operaciones básicas. Lo que realmente importa es entender los conceptos y el flujo del dinero.'),
('Solo las grandes empresas necesitan contabilidad', 'Toda entidad económica la requiere', 'Desde un pequeño negocio hasta tus finanzas personales se benefician de llevar cuentas claras.'),
('Los contadores solo hacen declaraciones de impuestos', 'Son asesores estratégicos clave', 'Analizan datos financieros para ayudar en la toma de decisiones y el crecimiento empresarial.'),
('La contabilidad es aburrida', 'Es dinámica y tiene enorme impacto', 'Resuelves problemas complejos y eres pieza clave en cualquier organización.'),
('Más activos siempre significa más éxito', 'Depende de la liquidez y rentabilidad', 'Puedes tener muchos edificios pero no tener efectivo para pagar la nómina.'),
('Las deudas siempre son malas', 'El endeudamiento estratégico ayuda a crecer', 'La deuda bien usada puede multiplicar la capacidad productiva.'),
('El software reemplazará a los contadores', 'La IA es una herramienta, no un sustituto', 'Se necesita el juicio humano para interpretar datos y tomar decisiones complejas.'),
('La contabilidad es solo para gente de negocios', 'Es una habilidad para la vida', 'Entender tus finanzas te da libertad y control sobre tu futuro.'),
('Es necesario ser un experto en Excel', 'Excel es solo una herramienta facilitadora', 'Lo importante es conocer la técnica contable; la herramienta se aprende con la práctica.');

-- News (Expanded)
INSERT INTO news (title, content, category) VALUES 
('Nuevas NIF 2026', 'Actualización de las Normas de Información Financiera para finales de año.', 'Fiscal'),
('IA en la Contabilidad', 'Cómo la inteligencia artificial está automatizando el registro de pólizas.', 'Tecnología'),
('DebiHaby v2.0', 'Lanzamiento de la nueva versión con ruta de aprendizaje personalizada.', 'Educación'),
('Reformas Fiscales 2025', 'Lo que debes saber sobre los nuevos impuestos digitales.', 'Fiscal'),
('Emprendimiento y Finanzas', '5 consejos para llevar la contabilidad de tu primera startup.', 'Economía'),
('Criptoactivos en el Balance', '¿Cómo se deben registrar las criptomonedas en la contabilidad formal?', 'Tecnología');

INSERT INTO expert_questions (user_id, question, answer, status) VALUES 
(1, '¿Qué es el IVA?', 'El Impuesto al Valor Agregado (IVA) es un impuesto indirecto sobre el consumo de bienes y servicios. En México la tasa general es del 16% y del 8% en zona fronteriza. Lo paga el consumidor final pero lo recauda el vendedor para entregarlo al SAT.', 'answered'),
(3, '¿Cuándo una empresa debe llevar contabilidad formal?', 'En México, toda persona moral (empresa constituida) está obligada a llevar contabilidad formal desde su constitución. Las personas físicas con actividad empresarial también, dependiendo de sus ingresos. El SAT requiere que la contabilidad se lleve en sistemas electrónicos aprobados.', 'answered'),
(3, '¿Qué diferencia hay entre utilidad bruta y utilidad neta?', 'La Utilidad Bruta = Ventas − Costo de Ventas. La Utilidad Neta es el resultado final después de restar todos los gastos operativos, financieros e impuestos. La utilidad neta es la ganancia real que queda para los dueños al final del período.', 'answered');

