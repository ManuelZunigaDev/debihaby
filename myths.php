<?php
session_start();
require_once 'includes/config.php';

$myths = [
    [
        "myth" => "La contabilidad es solo para matemáticos expertos.",
        "reality" => "Es pura lógica y organización.",
        "explanation" => "Cualquiera puede aprender si entiende los principios básicos de orden y registro."
    ],
    [
        "myth" => "Solo sirve para pagar impuestos.",
        "reality" => "Es la mejor herramienta para tomar decisiones.",
        "explanation" => "Sin contabilidad, no sabes si realmente estás ganando o perdiendo dinero."
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mitos y Realidades - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .myth-container { max-width: 800px; margin: 3rem auto; }
        .myth-card { perspective: 1000px; height: 300px; margin-bottom: 2rem; cursor: pointer; }
        .myth-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.8s; transform-style: preserve-3d; }
        .myth-card:hover .myth-inner { transform: rotateY(180deg); }
        .myth-front, .myth-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .myth-front { background: #fee2e2; color: #991b1b; }
        .myth-back { background: #dcfce7; color: #166534; transform: rotateY(180deg); }
    </style>
</head>
<body>
    <div class="myth-container">
        <h1 style="text-align: center;">🤔 ¿Mito o Realidad?</h1>
        <p style="text-align: center; margin-bottom: 3rem;">Pasa el mouse sobre las tarjetas para descubrir la verdad.</p>
        
        <?php foreach ($myths as $m): ?>
            <div class="myth-card">
                <div class="myth-inner">
                    <div class="myth-front">
                        <h2>MITO</h2>
                        <p style="font-size: 1.5rem;">"<?php echo $m['myth']; ?>"</p>
                    </div>
                    <div class="myth-back">
                        <h2>REALIDAD</h2>
                        <p style="font-size: 1.5rem; font-weight: bold;"><?php echo $m['reality']; ?></p>
                        <p style="margin-top: 1rem;"><?php echo $m['explanation']; ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div style="text-align: center; margin-top: 4rem;">
             <a href="dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
        </div>
    </div>
</body>
</html>
