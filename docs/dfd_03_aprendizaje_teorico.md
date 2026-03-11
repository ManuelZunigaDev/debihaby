#### Diagrama de flujo: Aprendizaje Teórico (Lecciones y Diagnóstico)

Representa cómo un estudiante interactúa con la parte teórica del sistema, respondiendo un cuestionario diagnóstico y obteniendo retroalimentación (Gamificación).

```mermaid
flowchart TD
    Inicio([Menú de Lecciones]) --> Seleccionar[Seleccionar Lección <br> o Diagnóstico]
    Seleccionar --> Desplegar[Desplegar Contenido Teórico / <br> Preguntas]
    Desplegar --> Responder[/Seleccionar Respuestas/]
    Responder --> Evaluar[Validar Respuestas VS <br> Opciones Correctas]
    Evaluar --> Calculo[Cálculo de Resultados]
    Calculo --> BD[(Actualizar: estadisticas <br> y progreso)]
    BD --> MostrarFeedback[/Mostrar Retroalimentación, <br> Puntuación Obtenida/]
    MostrarFeedback --> Fin([Fin de la Evaluación])

    %% Estilo general tipo Visio/DIA
    classDef default fill:#dae8fc,stroke:#6c8ebf,stroke-width:2px,color:#000;
```
