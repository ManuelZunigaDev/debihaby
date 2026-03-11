# Requerimientos y Funciones Faltantes a Implementar en la Plataforma DebiHaby

Basado estrictamente en los apartados **3.3.3** y **3.3.4** del documento del proyecto proporcionado, a continuación se detallan las funcionalidades y requerimientos técnicos que **aún no han sido implementados** en el sistema actual y que son indispensables para su conclusión.

---

## 1. Módulos Contables Prácticos e Interactivos
Actualmente el sistema permite la consulta de lecciones teóricas y resolución de cuestionarios de diagnóstico, pero carece de la parte de práctica e interactividad en registros contables estipulada en el documento.

Funciones a desarrollar:
*   **Creación y administración de ejercicios contables:** Una sección donde los docentes puedan cargar "Casos Prácticos" para que los alumnos los resuelvan.
*   **Simulación de registros de pólizas de ingresos, egresos y diario:** Formularios interactivos donde el alumno capture operaciones indicando las cuentas contables, montos de cargos y abonos.
*   **Generación automática del libro diario y libro mayor:** El sistema deberá procesar las pólizas hechas por el alumno y generar/visualizar en tiempo real el acomodo en formato de Libro Diario y en las "Cuentas T" del Libro Mayor.
*   **Elaboración de balanza de comprobación:** Una tabla resumen generada a partir del mayor que concentre de forma equilibrada movimientos y saldos.
*   **Generación de estados financieros básicos:** A partir de los saldos anteriores, la vista tabular del **Estado de Resultados** (Ingresos y Egresos) y el **Balance General** (Activo, Pasivo y Capital).
*   **Retroalimentación inmediata ante errores en los registros:** Un sistema de validación (por ejemplo, validar que los cargos sean iguales a los abonos) en tiempo real para avisar al alumno que cometió un error contable en sus pólizas.

---

## 2. Gestión de Usuarios y Roles Específicos
Tu base de datos actual (`users`) tiene implementado los roles `student` y `admin`. Falta implementar el perfil correspondiente a los docentes, como lo dicta el documento.

Funciones a desarrollar:
*   **Gestión de perfiles diferenciados (Docente):** Añadir el rol de docente en la base de datos y en el sistema de registro/login.
*   **Panel Docente Orientado a Supervisión:** Asegurar que los usuarios docentes cumplan con el requerimiento de:
    *   Administración de contenidos y ejercicios.
    *   Supervisión de actividades de los alumnos.
    *   **Generación de reportes de desempeño académico:** Dashboard para consultar rápidamente el avance estadístico, nivel y rachas de los estudiantes a su cargo.

---

## 3. Histórico Educativo
*   **Almacenamiento del historial de prácticas:** Tanto el alumno como el docente deben tener acceso a consultar todo el registro histórico de operaciones, pólizas y ejercicios desarrollados en distintos periodos de tiempo, almacenados de manera permanente en la base de datos de cada estudiante.

---

## 4. Requerimientos Técnicos
Dentro del apartado *Requerimientos técnicos*, el sistema ya cubre gran parte mediante PHP/MySQL, pero hay un requisito de infraestructura explícito pendiente:

*   **Implementar copias de seguridad (Backups) periódicas de la base de datos:** Se deberá instalar o desarrollar algún script (o cronjob en el servidor de alojamiento local o externo) que de acuerdo a las políticas del CBTis 171 realice un volcado (`mysqldump` o equivalente) automático periódicamente, para evitar la pérdida de información de los alumnos.

---

## ✅ Lo que SÍ cumple o va por el camino correcto
Según el mismo documento, el desarrollo actual en este punto sí respeta los siguientes requerimientos:
*   Tiene un registro y autenticación de usuarios mediante credenciales seguras (contraseñas encriptadas en la tabla de usuarios).
*   Opera bajo una arquitectura que permite su acceso desde navegadores web (sistema web cliente-servidor).
*   Se ejecuta sobre equipos básicos gracias a ser una aplicación basada en web.
*   Utiliza un gestor de base de datos relacional (MySQL) y mecanismos básicos de seguridad (PDO / consultas preparadas para proteger datos).
