## Fragmentos de Código Relevantes — DebiHaby

A continuación, se presentan los fragmentos de código más representativos de la plataforma **DebiHaby**, desarrollada con PHP, MySQL y JavaScript. Estos extractos ilustran los módulos funcionales principales del sistema.

---

### Fragmento 1: Autenticación de Usuarios — `login.php`

Este fragmento corresponde al módulo de inicio de sesión. Se utiliza una **consulta parametrizada con PDO** (PHP Data Objects) para prevenir inyecciones SQL. La función `password_verify()` compara de forma segura la contraseña ingresada contra el hash almacenado con `bcrypt` en la base de datos.

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
```
> **Tecnologías aplicadas:** PHP, PDO, Sesiones PHP, `password_verify()`.

---

### Fragmento 2: Registro de Nuevo Usuario — `register.php`

Este fragmento implementa el registro de alumnos. Verifica que el usuario o correo no esté duplicado, aplica cifrado de contraseña con `password_hash()` y realiza **tres INSERT** en una sola transacción: una fila en `usuarios`, una en `estadisticas` y una en `progreso` para activar la primera lección.

```php
// Verificar duplicado de usuario o correo
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);

if ($stmt->fetch()) {
    $error = 'El usuario o correo ya están registrados.';
} else {
    // Encriptar contraseña con bcrypt
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password, full_name, age, academic_level)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$username, $email, $hashedPassword, $full_name, $age, $academic_level]);
    $userId = $pdo->lastInsertId();

    // Inicializar estadísticas de gamificación
    $stmt = $pdo->prepare("INSERT INTO user_stats (user_id) VALUES (?)");
    $stmt->execute([$userId]);

    // Activar la primera lección disponible
    $stmt = $pdo->prepare(
        "INSERT INTO user_progress (user_id, lesson_id, status) VALUES (?, 1, 'available')"
    );
    $stmt->execute([$userId]);

    $_SESSION['user_id'] = $userId;
    header('Location: dashboard.php');
    exit;
}
```
> **Tecnologías aplicadas:** PHP, PDO, `password_hash()`, Sesiones PHP, INSERT en cascada.

---

### Fragmento 3: Diagnóstico Inicial y Asignación de Nivel — `diagnosis.php`

Este módulo calcula la puntuación del cuestionario diagnóstico y asigna automáticamente un nivel de conocimiento al estudiante (`principiante`, `intermedio_basico` o `intermedio`). Luego actualiza dos tablas: el `nivel_conocimiento` del usuario y sus `puntos` en gamificación.

```php
$questions = json_decode(file_get_contents('db/diagnosis_questions.json'), true);
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $totalPoints = 0;

    foreach ($questions as $q) {
        $totalPoints += $q['points'];
        if (isset($_POST['q' . $q['id']]) && $_POST['q' . $q['id']] == $q['answer']) {
            $score += $q['points'];
        }
    }

    // Calcular porcentaje y asignar nivel
    $percentage = ($score / $totalPoints) * 100;
    $level = 'principiante';
    if ($percentage >= 80) $level = 'intermedio';
    elseif ($percentage >= 50) $level = 'intermedio_basico';

    // Actualizar nivel de conocimiento en la tabla usuarios
    $stmt = $pdo->prepare("UPDATE users SET knowledge_level = ? WHERE id = ?");
    $stmt->execute([$level, $userId]);

    // Agregar puntos de bonificación a estadísticas
    $stmt = $pdo->prepare("UPDATE user_stats SET points = points + ? WHERE user_id = ?");
    $stmt->execute([$score, $userId]);

    header('Location: dashboard.php?diagnosis=success');
    exit;
}
```
> **Tecnologías aplicadas:** PHP, JSON, PDO, Lógica de Niveles por porcentaje.

---

### Fragmento 4: Módulo de Consulta a Expertos — `experts_handler.php`

Este controlador recibe la pregunta de un estudiante mediante un formulario POST, valida que el usuario esté autenticado mediante su sesión activa, y la guarda en la tabla `expert_questions` con estado `pending` para que un administrador la responda posteriormente.

```php
// Validar sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $userId = $_SESSION['user_id'];
    $question = trim($_POST['question']);

    if (!empty($question)) {
        $stmt = $pdo->prepare(
            "INSERT INTO expert_questions (user_id, question) VALUES (?, ?)"
        );
        $stmt->execute([$userId, $question]);

        // Redirigir al dashboard con confirmación
        header('Location: dashboard.php?expert_success=1#experts');
        exit;
    }
}
```
> **Tecnologías aplicadas:** PHP, PDO, Control de Sesiones, Redirección HTTP.

---

### Fragmento 5: Lección Interactiva con Arrastrar y Soltar — `lesson.php`

Este fragmento muestra la lógica de JavaScript que habilita el arrastre de elementos (drag and drop) en los ejercicios interactivos. Cuando el alumno suelta un activo en la zona correcta, se incrementa el contador y, al alcanzar el total requerido, se muestra la pantalla de éxito y se registra el progreso.

```javascript
const dragItems = document.querySelectorAll('.drag-item');
const dropZone = document.getElementById('assets-chest');
let correctCount = 0;
const totalNeeded = Array.from(dragItems)
    .filter(item => item.dataset.type === 'activo').length;

// Iniciar arrastre
dragItems.forEach(item => {
    item.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('type', item.dataset.type);
        e.dataTransfer.setData('text', item.innerText);
        item.style.opacity = '0.5';
    });
});

// Soltar en la zona de activos
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    const type = e.dataTransfer.getData('type');
    const text = e.dataTransfer.getData('text');

    if (type === 'activo') {
        const tag = document.createElement('div');
        tag.className = 'tag-success';
        tag.innerText = text;
        dropZone.appendChild(tag);

        correctCount++;
        document.getElementById('correct-count').innerText = correctCount;

        // ¡Completó todos los activos!
        if (correctCount === totalNeeded) {
            setTimeout(() => {
                document.getElementById('result-overlay').style.display = 'flex';
                updateProgress(); // Llamada AJAX para guardar progreso
            }, 500);
        }
    } else {
        Swal.fire({
            title: '¡Oops!',
            text: 'Eso parece una deuda (Pasivo). Intenta con bienes de la empresa.',
            icon: 'warning'
        });
    }
});
```
> **Tecnologías aplicadas:** JavaScript (Vanilla), Drag and Drop API, SweetAlert2.

---

### Fragmento 6: Navegación Multi-paso de Lección — `lesson.php` (PHP)

Este fragmento ilustra la lógica de navegación entre los cinco pasos pedagógicos de una lección (modelo de las 5Es: Enganche, Exploración, Explicación, Elaboración y Evaluación). Se determina el paso actual, el siguiente, y el índice de avance del alumno usando un arreglo ordenado.

```php
// Definir el orden pedagógico de los pasos (Modelo 5Es)
$stepsOrder = ['enganche', 'exploracion', 'explicacion', 'elaboracion', 'evaluacion'];
$step = $_GET['step'] ?? 'enganche';
$currentIndex = array_search($step, $stepsOrder);

// Cargar contenido del paso actual desde JSON
$allContent = json_decode(file_get_contents('db/lessons_content.json'), true);
$lessonContent = $allContent[$lessonId] ?? null;
$currentStepData = $lessonContent['steps'][$step] ?? null;

// Calcular el siguiente paso
$nextStep = ($currentIndex < count($stepsOrder) - 1)
    ? $stepsOrder[$currentIndex + 1]
    : null; // null = último paso → mostrar botón "Finalizar"
```

> **Tecnologías aplicadas:** PHP, JSON, Modelo Pedagógico 5Es, Lógica de Pasos Secuenciales.
