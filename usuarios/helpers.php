<?php
function get_absolute_image_url($relative_path, $type = 'medico') {
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/proyectoimesys/';
    
    // Limpiar la ruta recibida
    $clean_path = str_replace(['uploads/medicos/', 'uploads\\medicos\\', 'uploads/'], '', $relative_path);
    $clean_path = basename($clean_path);
    
    // Determinar la carpeta según el tipo
    $folder = ($type === 'medico') ? 'uploads/medicos/' : 'uploads/';
    
    // Ruta absoluta en el servidor
    $absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/proyectoimesys/' . $folder . $clean_path;
    
    // Verificar si el archivo existe
    if ($relative_path && file_exists($absolute_path)) {
        return $base_url . $folder . $clean_path;
    }
    return false;
}
?>