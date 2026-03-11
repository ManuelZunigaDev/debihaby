#### Diagrama de flujo: Inicio de Sesión

Este diagrama representa el algoritmo de validación para que un alumno o docente ingrese a la plataforma, basado en el flujo de verificación de credenciales.

```mermaid
flowchart TD
    Inicio([Inicio]) --> Pantalla[Inicio de sesión]
    Pantalla --> Ingreso[Ingreso de las credenciales]
    Ingreso --> Credenciales[/Credenciales/]
    Credenciales --> Validar[Validación de datos]
    Validar --> Consulta[(Consulta <br> en base de <br> datos)]
    Consulta --> Decision{¿El <br> usuario <br> existe?}
    Decision -- No --> Error[/Usuario o <br> contraseña <br> incorrectos/]
    Error --> Pantalla
    Decision -- Si --> Exito([Acceso exitoso])

    %% Estilo general tipo Visio/DIA
    classDef default fill:#dae8fc,stroke:#6c8ebf,stroke-width:2px,color:#000;
```
