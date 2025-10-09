<?php

require_once 'conexion.php';

class ModeloTroquelado{

    /*=============================================
    =     GUARDAR TROQUELADO (INSERT)            =
    =============================================*/
    static public function mdlGuardarTroquelado($tabla, $datos){

        $stmt = Conexion::conectar()->prepare(
          "INSERT INTO $tabla(nombre, FuerzaTroquelado, InspeccionVisual, VelocidadTroquelado)
           VALUES (:nombre, :FuerzaTroquelado, :InspeccionVisual, :VelocidadTroquelado)"
        );

        $stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);

        // Cambiamos PARAM_INT a PARAM_STR para floats
        $stmt -> bindParam(":FuerzaTroquelado", $datos["FuerzaTroquelado"], PDO::PARAM_STR);
        $stmt -> bindParam(":InspeccionVisual", $datos["InspeccionVisual"], PDO::PARAM_STR);
        $stmt -> bindParam(":VelocidadTroquelado", $datos["VelocidadTroquelado"], PDO::PARAM_STR);

        if ($stmt -> execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt = null;
    }

    /*=============================================
    =     MOSTRAR TROQUELADO                     =
    =============================================*/
    static public function mdlMostrarTroquelado($tabla, $datos, $valor){

        if ($datos !== null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $datos = :$datos");
            $stmt -> bindParam(":".$datos, $valor, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
            $stmt -> execute();
            return $stmt -> fetchAll();
        }
    }

    /*=============================================
    =     EDITAR PIEZA TROQUELADO (UPDATE)       =
    =============================================*/
    static public function mdlEditarPiezaTro($tabla, $datos){

        $stmt = Conexion::conectar()->prepare(
          "UPDATE $tabla SET 
              nombre = :nombre, 
              FuerzaTroquelado = :FuerzaTroquelado, 
              InspeccionVisual = :InspeccionVisual, 
              VelocidadTroquelado = :VelocidadTroquelado
           WHERE id = :id"
        );

        $stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);

        // Cambiamos PARAM_INT a PARAM_STR para floats
        $stmt -> bindParam(":FuerzaTroquelado", $datos["FuerzaTroquelado"], PDO::PARAM_STR);
        $stmt -> bindParam(":InspeccionVisual", $datos["InspeccionVisual"], PDO::PARAM_STR);
        $stmt -> bindParam(":VelocidadTroquelado", $datos["VelocidadTroquelado"], PDO::PARAM_STR);

        if ($stmt -> execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt = null;
    }

    /*=============================================
    =     BORRAR PIEZA (DELETE)                  =
    =============================================*/
    static public function mdlBorrarPieza($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

        if ($stmt -> execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt = null;
    }

}