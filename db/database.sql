-- DebiHaby Database Schema - MULTI-COURSE VERSION
CREATE DATABASE IF NOT EXISTS debihaby_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE debihaby_db;

-- --- CORE TABLES ---
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS user_progress;
DROP TABLE IF EXISTS user_stats;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255) DEFAULT 'assets/debi_pet.png',
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_stats (
    user_id INT PRIMARY KEY,
    points INT DEFAULT 0,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    streak INT DEFAULT 0,
    last_activity DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    icon VARCHAR(50) DEFAULT 'fa-graduation-cap',
    color VARCHAR(20) DEFAULT '#2196F3',
    order_index INT DEFAULT 0
);

CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    order_index INT DEFAULT 0,
    xp_reward INT DEFAULT 100,
    icon_class VARCHAR(50) DEFAULT 'fa-book',
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE user_progress (
    user_id INT,
    lesson_id INT,
    status ENUM('locked', 'available', 'completed') DEFAULT 'locked',
    score INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    PRIMARY KEY (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- INITIAL DATA
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE user_progress;
TRUNCATE TABLE user_stats;
TRUNCATE TABLE lessons;
TRUNCATE TABLE courses;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO users (id, username, email, password, full_name, role) VALUES 
(1, 'user', 'user@debihaby.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuario Estudiante', 'student'),
(2, 'admin', 'admin@debihaby.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Debi', 'admin');

INSERT INTO user_stats (user_id, points, level, experience, streak) VALUES 
(1, 0, 1, 0, 0),
(2, 5000, 5, 0, 0);

INSERT INTO courses (id, title, description, category, icon, color, order_index) VALUES 
(1, 'Fundamentos Contables', 'Los pilares básicos de la contabilidad empresarial.', 'Básico', 'fa-coins', '#FF9800', 1),
(2, 'Ciclo de Deudas (Pasivos)', 'Entender y gestionar lo que la empresa debe.', 'Intermedio', 'fa-file-invoice-dollar', '#f44336', 2),
(3, 'Patrimonio y Capital', 'El valor real de los dueños y socios.', 'Intermedio', 'fa-vault', '#4CAF50', 3),
(4, 'Rendimiento Financiero', 'Ingresos, gastos y utilidades.', 'Avanzado', 'fa-chart-line', '#2196F3', 4),
(5, 'Control y Auditoría', 'Cierres, reportes y veracidad contable.', 'Avanzado', 'fa-shield-check', '#9c27b0', 5);

INSERT INTO lessons (course_id, title, description, order_index, xp_reward, icon_class) VALUES 
-- Curso 1: Fundamentos
(1, '¿Qué es un Activo?', 'Conceptos básicos de bienes.', 1, 100, 'fa-coins'),
(1, 'Activo Circulante', 'Efectivo e inventarios.', 2, 120, 'fa-wallet'),
(1, 'Activo Fijo', 'Maquinaria y edificios.', 3, 120, 'fa-building'),
(1, 'Activo Diferido', 'Pagos anticipados.', 4, 120, 'fa-clock'),
(1, 'Inventarios Físicos', 'Control de mercancía real.', 5, 150, 'fa-boxes-stacked'),
-- Curso 2: Pasivos
(2, '¿Qué es un Pasivo?', 'Obligaciones con terceros.', 1, 100, 'fa-file-invoice-dollar'),
(2, 'Pasivo a Corto Plazo', 'Deudas inmediatas.', 2, 120, 'fa-calendar-day'),
(2, 'Pasivo a Largo Plazo', 'Hipotecas y créditos.', 3, 120, 'fa-calendar-check'),
(2, 'Cuentas por Pagar', 'Gestión de proveedores.', 4, 130, 'fa-truck'),
(2, 'Préstamos Bancarios', 'Intereses y amortización.', 5, 150, 'fa-bank'),
-- Curso 3: Capital
(3, 'El Capital Contable', 'Patrimonio neto.', 1, 150, 'fa-vault'),
(3, 'Capital Social', 'Aportaciones de socios.', 2, 130, 'fa-users'),
(3, 'Utilidades Retenidas', 'Ganancias no repartidas.', 3, 140, 'fa-piggy-bank'),
(3, 'Reservas Legales', 'Fondos de protección.', 4, 140, 'fa-gavel'),
(3, 'Dividendos', 'Reparto de beneficios.', 5, 160, 'fa-hand-holding-dollar'),
-- Curso 4: Rendimiento
(4, 'La Ecuación Contable', 'Activo = Pasivo + Capital.', 1, 130, 'fa-scale-balanced'),
(4, 'Partida Doble', 'Cargo y Abono.', 2, 140, 'fa-plus-minus'),
(4, 'Ingresos Operativos', 'Ventas reales.', 3, 140, 'fa-cart-shopping'),
(4, 'Gastos de Operación', 'Nómina y luz.', 4, 140, 'fa-lightbulb'),
(4, 'Punto de Equilibrio', '¿Cuándo empezamos a ganar?', 5, 180, 'fa-bullseye'),
-- Curso 5: Control
(5, 'El Balance General', 'Foto del negocio.', 1, 160, 'fa-table-columns'),
(5, 'Estado de Resultados', 'Ganancia o Pérdida.', 2, 160, 'fa-file-lines'),
(5, 'Cierre Mensual', 'Cuadre de cuentas.', 3, 180, 'fa-calendar-check'),
(5, 'Auditoría Básica', 'Verificar que no falte nada.', 4, 200, 'fa-magnifying-glass-chart'),
(5, 'Ética Contable', 'Transparencia y legalidad.', 5, 250, 'fa-flag-checkered');

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

