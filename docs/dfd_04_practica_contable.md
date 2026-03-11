#### Diagrama de flujo: Práctica Contable Interactiva (Pólizas y Libro Diario)

Es el algoritmo principal de funcionalidad práctica; asegura que cada asiento que realiza el alumno cumpla con la regla de la Partida Doble antes de procesar la póliza e impactar los libros.

```mermaid
flowchart TD
    Inicio([Panel de Casos Prácticos]) --> SeleccionarCaso[Elegir Ejercicio <br> Contable]
    SeleccionarCaso --> LeerProblema[/Lectura del Caso <br> y Montos/]
    LeerProblema --> SeleccionarCuentas[Selección de Cuentas <br> del Catálogo]
    SeleccionarCuentas --> AsignarMontos[/Ingreso de <br> Cargos y Abonos/]
    AsignarMontos --> ValidarPartida[Calcular total de <br> Cargos VS Abonos]
    ValidarPartida --> Cuadre{¿Suma Cargos <br> = <br> Suma Abonos?}

    Cuadre -- No --> ErrorPartida[/Mostrar alerta <br> de descuadre <br> en póliza/]
    ErrorPartida --> SeleccionarCuentas

    Cuadre -- Si --> RegistroExito[(Registrar Póliza <br> en Libro Diario)]
    RegistroExito --> PaseMayor[(Pase automático <br> a Libro Mayor)]
    PaseMayor --> Fin([Póliza completa])

    %% Estilo general tipo Visio/DIA
    classDef default fill:#dae8fc,stroke:#6c8ebf,stroke-width:2px,color:#000;
```
