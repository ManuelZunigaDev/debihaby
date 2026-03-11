#### Diagrama de flujo: Panel de Administrador (Gestión del Sistema)

Flujo de las operaciones principales de un administrador: gestionar el contenido y las cuentas de usuarios de la plataforma DebiHaby.

```mermaid
flowchart TD
    Inicio([Inicio de sesión - Admin]) --> Dashboard[Panel de Control <br> Administrador]
    Dashboard --> Accion{¿Qué elemento <br> gestionar?}
    Accion -- Usuarios --> GestionU[Crear, Editar o <br> Eliminar Perfiles]
    Accion -- Contenido --> GestionC[Publicar Noticias, <br> Mitos o Lecciones]
    Accion -- Soporte --> GestionS[Responder Dudas <br> de Expertos]
    GestionU --> ModificarBD[(Aplicar cambios en: <br> usuarios / lecciones / <br> preguntas_expertos)]
    GestionC --> ModificarBD
    GestionS --> ModificarBD
    ModificarBD --> Exito([Confirmación de <br> Acción Exitosa])
    Exito --> Dashboard

    %% Estilo general tipo Visio/DIA
    classDef default fill:#dae8fc,stroke:#6c8ebf,stroke-width:2px,color:#000;
```
