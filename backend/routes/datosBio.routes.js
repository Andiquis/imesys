// routes/datosBio.routes.js

/**
Crear un nuevo dato biométrico
Listar todos los datos biométricos
Buscar por id_dato 
Buscar por id_usuario 
Actualizar por id_dato 
Eliminar por id_dato 
*/

import { Router } from "express";
import pool from "../config/db.js"; // Cambiado a import y asegurándonos que db.js exporte pool

const router = Router();

// Crear un nuevo dato biométrico
router.post('/', async (req, res) => {
    const {
        id_usuario,
        peso,
        altura,
        presion_arterial,
        frecuencia_cardiaca,
        nivel_glucosa,
        descripcion_resultado,
        resultado_prediccion
    } = req.body;

    try {
        const [result] = await pool.query(`
            INSERT INTO datos_biometricos (
                id_usuario, peso, altura, presion_arterial,
                frecuencia_cardiaca, nivel_glucosa,
                descripcion_resultado, resultado_prediccion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        `, [
            id_usuario,
            peso,
            altura,
            presion_arterial,
            frecuencia_cardiaca,
            nivel_glucosa,
            descripcion_resultado,
            resultado_prediccion
        ]);

        res.status(201).json({ message: 'Dato biométrico creado correctamente', id: result.insertId });
    } catch (err) {
        console.error('Error al insertar dato biométrico:', err);
        res.status(500).json({ message: 'Error en el servidor' });
    }
});

// Obtener todos los datos biométricos
router.get('/', async (req, res) => {
    try {
        const [results] = await pool.query(`SELECT * FROM datos_biometricos`);
        res.status(200).json(results);
    } catch (err) {
        console.error('Error al obtener datos biométricos:', err);
        res.status(500).json({ message: 'Error en el servidor' });
    }
});

// Obtener un dato biométrico por id_dato
router.get('/:id', async (req, res) => {
    const { id } = req.params;

    try {
        const [result] = await pool.query(`SELECT * FROM datos_biometricos WHERE id_dato = ?`, [id]);

        if (result.length === 0) {
            return res.status(404).json({ message: 'Dato biométrico no encontrado' });
        }
        res.status(200).json(result[0]);
    } catch (err) {
        console.error('Error al obtener dato biométrico:', err);
        res.status(500).json({ message: 'Error en el servidor' });
    }
});

// Obtener datos biométricos por id_usuario
router.get('/usuario/:id_usuario', async (req, res) => {
    const { id_usuario } = req.params;

    try {
        const [results] = await pool.query(`SELECT * FROM datos_biometricos WHERE id_usuario = ?`, [id_usuario]);

        if (results.length === 0) {
            return res.status(404).json({ message: 'No se encontraron datos biométricos para este usuario' });
        }
        res.status(200).json(results);
    } catch (err) {
        console.error('Error al obtener datos biométricos por usuario:', err);
        res.status(500).json({ message: 'Error en el servidor' });
    }
});

// Actualizar un dato biométrico por id_dato
router.put('/:id', async (req, res) => {
    const { id } = req.params;
    const {
        peso,
        altura,
        presion_arterial,
        frecuencia_cardiaca,
        nivel_glucosa,
        descripcion_resultado,
        resultado_prediccion
    } = req.body;

    try {
        await pool.query(`
            UPDATE datos_biometricos SET
                peso = ?, altura = ?, presion_arterial = ?, frecuencia_cardiaca = ?,
                nivel_glucosa = ?, descripcion_resultado = ?, resultado_prediccion = ?
            WHERE id_dato = ?
        `, [
            peso,
            altura,
            presion_arterial,
            frecuencia_cardiaca,
            nivel_glucosa,
            descripcion_resultado,
            resultado_prediccion,
            id
        ]);

        res.status(200).json({ message: 'Dato biométrico actualizado correctamente' });
    } catch (err) {
        console.error('Error al actualizar dato biométrico:', err);
        res.status(500).json({ message: 'Error en el servidor' });
    }
});

// Eliminar un dato biométrico por id_dato
router.delete('/:id', async (req, res) => {
    const { id } = req.params;

    try {
        await pool.query(`DELETE FROM datos_biometricos WHERE id_dato = ?`, [id]);
        res.status(200).json({ message: 'Dato biométrico eliminado correctamente' });
    } catch (err) {
        console.error('Error al eliminar dato biométrico:', err);
        res.status(500).json({ message: 'Error en el servidor' });
    }
});

export default router;
