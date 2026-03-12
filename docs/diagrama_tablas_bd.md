#### 3.4.2.2.1. Diagrama de tablas

El siguiente diagrama Entidad-Relación (E-R) representa la estructura final de la base de datos de **DebiHaby**, incluyendo tanto las tablas actuales para la gestión de usuarios y teoría, como las nuevas entidades requeridas para el funcionamiento de los simuladores contables (catálogo de cuentas, libro diario, etc.).

```mermaid
erDiagram
    %% Gestión de Usuarios y Roles
    USUARIOS {
        int id PK "Auto-incremental"
        varchar(50) usuario "Único"
        varchar(100) correo "Único"
        varchar(255) contrasena "Encriptada"
        varchar(100) nombre_completo
        enum rol "estudiante, docente, admin"
        enum nivel_conocimiento
    }

    ESTADISTICAS {
        int usuario_id PK, FK "Relacionado a usuarios"
        int puntos
        int nivel
        int experiencia
        int racha
        date ultima_actividad
    }

    %% Progreso Teórico
    LECCIONES {
        int id PK
        varchar(100) titulo
        enum categoria "Activos, Pasivos, etc."
        int recompensa_xp
    }

    PROGRESO {
        int usuario_id PK, FK
        int leccion_id PK, FK
        enum estado "bloqueada, disponible, completada"
        int puntaje
    }

    %% Simulador Contable
    CATALOGO_CUENTAS {
        int id PK
        varchar(10) codigo "Ej. 101, 201"
        varchar(100) nombre "Ej. Bancos, Proveedores"
        enum tipo "Activo, Pasivo, Capital, Resultados"
        enum naturaleza "Deudora, Acreedora"
    }

    EJERCICIOS {
        int id PK
        int docente_id FK "Usuario creador"
        varchar(200) titulo
        text descripcion "Redacción del caso"
        date fecha_creacion
    }

    POLIZAS {
        int id PK
        int ejercicio_id FK
        int estudiante_id FK "Alumno que responde"
        enum tipo "Ingreso, Egreso, Diario"
        date fecha_envio
        boolean es_correcto "Validación del cuadre"
    }

    MOVIMIENTOS_DIARIO {
        int id PK
        int poliza_id FK
        int cuenta_id FK "Referencia al catálogo"
        decimal cargo "Monto"
        decimal abono "Monto"
    }

    %% Tablas Adicionales (Sección Informativa y Panel Expertos)
    NOTICIAS {
        int id PK
        varchar(200) titulo
        text contenido
        varchar(50) categoria
        timestamp fecha_creacion
    }

    MITOS {
        int id PK
        text mito
        text realidad
        text explicacion
    }

    PREGUNTAS_EXPERTOS {
        int id PK
        int usuario_id FK "Alumno que pregunta"
        text pregunta
        text respuesta
        enum estado "pendiente, respondida"
        timestamp fecha_creacion
    }

    %% Relaciones
    USUARIOS ||--|| ESTADISTICAS : "tiene"
    USUARIOS ||--o{ PROGRESO : "logra"
    LECCIONES ||--o{ PROGRESO : "es cursada"

    USUARIOS ||--o{ EJERCICIOS : "crea (Docente)"
    USUARIOS ||--o{ POLIZAS : "resuelve (Estudiante)"
    USUARIOS ||--o{ PREGUNTAS_EXPERTOS : "pregunta (Estudiante) / responde (Docente)"
    EJERCICIOS ||--o{ POLIZAS : "contiene"

    POLIZAS ||--|{ MOVIMIENTOS_DIARIO : "se compone de"
    CATALOGO_CUENTAS ||--o{ MOVIMIENTOS_DIARIO : "se utiliza en"
```

**Descripción de las Entidades Principales:**

- **USERS y USER_STATS:** Controlan el acceso a la plataforma (estudiantes, docentes, administradores) y almacenan las métricas de gamificación (puntos, nivel, racha).
- **LESSONS y USER_PROGRESS:** Administran el contenido teórico y registran qué lecciones ha desbloqueado o completado el alumno.
- **CATALOGO_CUENTAS (Nuevo):** El catálogo oficial de cuentas que utilizarán los alumnos para armar sus registros. Define la naturaleza y el tipo de cada cuenta para la automatización de reportes.
- **EJERCICIOS y POLIZAS (Nuevo):** Almacenan los casos prácticos redactados por los docentes y las respuestas enviadas por los estudiantes.
- **MOVIMIENTOS_DIARIO (Nuevo):** Registra cada transacción individual (el cargo o el abono a una cuenta específica) que conforma la póliza de un estudiante, permitiendo calcular automáticamente si cuadra la partida doble.
- **NEWS, MYTHS y EXPERT_QUESTIONS:** Gestionan el contenido complementario informativo y el sistema de asesorías personalizadas de la plataforma.
