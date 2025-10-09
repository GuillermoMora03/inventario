

<?php

require_once 'conexion.php';

class ModeloFundicion {

    /**
     * Quota el identificador de tabla si no viene con backticks.
     */
    private static function q($name) {
        return (strpos($name, '`') === false) ? "`{$name}`" : $name;
    }

    /**
     * ============== INSERTAR (CREATE) ==============
     * Espera $datos = [ 'temp_horno' => int, 'temp_material' => int, 'clase' => 'buena'|'mala' ]
     */
    public static function mdlIngresarFundicion($tabla, $datos) {
        $T = self::q($tabla);
        $sql = "INSERT INTO $T (temp_horno, temp_material, clase) VALUES (:temp_horno, :temp_material, :clase)";
        $stmt = Conexion::conectar()->prepare($sql);

        $stmt->bindParam(":temp_horno", $datos["temp_horno"], PDO::PARAM_INT);
        $stmt->bindParam(":temp_material", $datos["temp_material"], PDO::PARAM_INT);
        $stmt->bindParam(":clase", $datos["clase"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /**
     * ============== MOSTRAR (READ) ==============
     * Si $item !== null, filtra por columna $item con valor $valor (solo columnas permitidas).
     * Columnas permitidas: id, temp_horno, temp_material, clase
     */
    public static function mdlMostrarFundicion($tabla, $item, $valor) {
        $T = self::q($tabla);

        $permitidas = ["id", "temp_horno", "temp_material", "clase"];
        if ($item !== null && in_array($item, $permitidas, true)) {
            $sql = "SELECT id, temp_horno, temp_material, clase FROM $T WHERE $item = :val";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":val", $valor, ($item === 'id' ? PDO::PARAM_INT : PDO::PARAM_STR));
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $sql = "SELECT id, temp_horno, temp_material, clase FROM $T ORDER BY id DESC";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /**
     * ============== EDITAR (UPDATE) ==============
     * Espera $datos = [ 'id' => int, 'temp_horno' => int, 'temp_material' => int, 'clase' => 'buena'|'mala' ]
     */
    public static function mdlEditarFundicion($tabla, $datos) {
        $T = self::q($tabla);
        $sql = "UPDATE $T SET temp_horno = :temp_horno, temp_material = :temp_material, clase = :clase WHERE id = :id";
        $stmt = Conexion::conectar()->prepare($sql);

        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt->bindParam(":temp_horno", $datos["temp_horno"], PDO::PARAM_INT);
        $stmt->bindParam(":temp_material", $datos["temp_material"], PDO::PARAM_INT);
        $stmt->bindParam(":clase", $datos["clase"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /**
     * ============== BORRAR (DELETE) ==============
     */
    public static function mdlBorrarFundicion($tabla, $id) {
        $T = self::q($tabla);
        $sql = "DELETE FROM $T WHERE id = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
}