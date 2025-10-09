<?php

require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/troquelado.controlador.php";
require_once "controladores/fundicion.controlador.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/troquelado.modelo.php";
require_once "modelos/fundicion.modelo.php";


$plantilla = new ControladorPlantilla();
$plantilla->Plantilla();
