#### Diagrama de flujo: Panel de Consulta a Expertos (Asesoría)

Flujo que permite a los estudiantes enviar preguntas contables, las cuales son atendidas por los **docentes de la especialidad** a través de su panel, garantizando asesoramiento especializado por parte de los profesores del plantel.

```mermaid
flowchart TD
    Inicio([Sección de Expertos]) --> Pregunta[Redactar Nueva <br> Pregunta Contable]
    Pregunta --> Enviar[/Enviar Pregunta/]
    Enviar --> BDPending[(Guardar en: preguntas_expertos <br> estado = pendiente)]
    BDPending --> DocPanel[Panel del Docente <br> (o Administrador)]
    DocPanel --> Responder[Docente redacta <br> Respuesta Especializada]
    Responder --> BDAnswered[(Actualizar: preguntas_expertos <br> estado = respondida)]
    BDAnswered --> Notificar[/Mostrar Respuesta <br> al Estudiante/]

    %% Estilo general tipo Visio/DIA
    classDef default fill:#dae8fc,stroke:#6c8ebf,stroke-width:2px,color:#000;
```
