# Tarea-2_Base_de_datos

# Integrantes
- Jose Pablo Reyes Rol: 202430028-8
- Joaquín Vásquez Rol: 202430005-9

# Instrucciones de Ejecución
1. Importar el archivo `BD/SaludUSM.sql` utilizando phpMyAdmin.
2. Ubicar la carpeta del proyecto dentro del directorio `htdocs` de XAMPP (o `/var/www/html` en su defecto).
3. Verificar que las credenciales de conexión en `PHP/conexion.php` coincidan con su servidor local.
4. Ingresar a `index.php` desde el navegador para acceder al Login.

# Usuarios de Prueba
*   **Administrador:** RUT: `admin` | Contraseña: `admin123`
*   **Médico (Multicentro/Multiespecialidad):** RUT: `11111111-1` | Contraseña: `123`
*   **Paciente:** RUT: `17000001-1` | Contraseña: `123`

# Supuestos Asumidos
1. **Horarios y Bloques:** Se asume que el horario de atención es de 08:00 a 18:00 horas, con bloques de atención de exactamente 30 minutos de duración.
2. **Eliminación de cuenta de Paciente:** Si un paciente elimina su cuenta, se asume que todas sus citas futuras en estado "Reservada" o "Confirmada" son eliminadas de la base de datos (o actualizadas a "Cancelada") para liberar la agenda del médico de forma inmediata.
3. **Actualización de citas vencidas:** Se asume que el cambio de estado de citas vencidas a "Cancelada" se ejecuta de forma dinámica al cargar el Dashboard del paciente o del médico, verificando si la `fecha_hora` es menor a `NOW()` y su estado es "Reservada" o "Confirmada".
4. **Desvinculación de médicos (Especialidad/Centro):** Si un Administrador intenta quitarle una especialidad o un centro a un médico que ya tiene citas futuras asociadas a dichas características, el sistema impedirá la acción mostrando un mensaje de error, exigiendo que las citas sean reasignadas o canceladas primero.
