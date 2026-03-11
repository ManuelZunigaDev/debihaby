#### Diagrama de flujo: Panel de Consulta a Expertos (Asesoría)

Flujo que permite a los estudiantes realizar preguntas contables que serán respondidas por los administradores o docentes expertos.

```mermaid
flowchart TD
    Inicio([Sección de Expertos]) --> Pregunta[Redactar Nueva <br> Pregunta Contable]
    Pregunta --> Enviar[/Enviar Pregunta/]
    Enviar --> BDPending[(Guardar Pregunta <br> como Pendiente en BD)]
    BDPending --> AdminPanel[Panel de Administrador / <br> Docente]
    AdminPanel --> Responder[Redactar Respuesta]
    Responder --> BDAnswered[(Actualizar Estado <br> de Pregunta a Respondida)]
    BDAnswered --> Notificar[/Mostrar Respuesta <br> al Estudiante/]

    %% Estilo general tipo Visio/DIA
    classDef default fill:#dae8fc,stroke:#6c8ebf,stroke-width:2px,color:#000;
```
