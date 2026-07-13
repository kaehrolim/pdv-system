<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Usuario.php';

$model = new Usuario($pdo);
$model->criar('Admin', 'admin@pdv.local', 'admin123', 'admin');

echo "Usuario admin criado.\n";