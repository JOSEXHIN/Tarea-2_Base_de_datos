CREATE DATABASE IF NOT EXISTS saludusm;
USE saludusm;

--  TABLAS DE DOMINIO
CREATE TABLE Prevision (
    id_prevision INT AUTO_INCREMENT PRIMARY KEY,
    nombre_prevision VARCHAR(50) NOT NULL
);

CREATE TABLE Estado_Cita (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estado VARCHAR(50) NOT NULL
);

CREATE TABLE Especialidad (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_especialidad VARCHAR(100) NOT NULL
);

-- TABLAS PRINCIPALES
CREATE TABLE Paciente (
    rut_paciente VARCHAR(10) PRIMARY KEY,
    nombre_paciente VARCHAR(100) NOT NULL,
    id_prevision INT NOT NULL,
    telefono VARCHAR(15),
    fecha_nacimiento DATE,
    sexo CHAR(1),
    clave VARCHAR(255) NOT NULL, 
    FOREIGN KEY (id_prevision) REFERENCES Prevision(id_prevision)
);

CREATE TABLE Medico (
    rut_medico VARCHAR(10) PRIMARY KEY,
    nombre_medico VARCHAR(100) NOT NULL,
    email_medico VARCHAR(100) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL 
);

CREATE TABLE Centro_Medico (
    codigo_centro INT AUTO_INCREMENT PRIMARY KEY,
    nombre_centro VARCHAR(100) NOT NULL,
    comuna VARCHAR(50) NOT NULL,
    region VARCHAR(50) NOT NULL
);

-- RELACIONES MUCHOS A MUCHOS
CREATE TABLE Medico_Especialidad (
    rut_medico VARCHAR(10),
    id_especialidad INT,
    PRIMARY KEY (rut_medico, id_especialidad),
    FOREIGN KEY (rut_medico) REFERENCES Medico(rut_medico) ON DELETE CASCADE,
    FOREIGN KEY (id_especialidad) REFERENCES Especialidad(id_especialidad) ON DELETE CASCADE
);

CREATE TABLE Medico_Centro (
    rut_medico VARCHAR(10),
    codigo_centro INT,
    PRIMARY KEY (rut_medico, codigo_centro),
    FOREIGN KEY (rut_medico) REFERENCES Medico(rut_medico) ON DELETE CASCADE,
    FOREIGN KEY (codigo_centro) REFERENCES Centro_Medico(codigo_centro) ON DELETE CASCADE
);

--  TABLAS DE TRANSACCIÓN (Citas y Atenciones)
CREATE TABLE Cita (
    n_cita INT AUTO_INCREMENT PRIMARY KEY,
    fecha_cita DATE NOT NULL,
    hora_cita TIME NOT NULL,
    id_estado INT NOT NULL,
    rut_paciente VARCHAR(10) NOT NULL,
    rut_medico VARCHAR(10) NOT NULL,
    codigo_centro INT NOT NULL,
    id_especialidad INT NOT NULL,
    FOREIGN KEY (id_estado) REFERENCES Estado_Cita(id_estado),
    FOREIGN KEY (rut_paciente) REFERENCES Paciente(rut_paciente) ON DELETE CASCADE,
    FOREIGN KEY (rut_medico) REFERENCES Medico(rut_medico),
    FOREIGN KEY (codigo_centro) REFERENCES Centro_Medico(codigo_centro),
    FOREIGN KEY (id_especialidad) REFERENCES Especialidad(id_especialidad)
);

CREATE TABLE Atencion (
    n_atencion INT AUTO_INCREMENT PRIMARY KEY,
    n_cita INT NOT NULL UNIQUE,
    motivo_consulta TEXT NOT NULL,
    observaciones TEXT,
    FOREIGN KEY (n_cita) REFERENCES Cita(n_cita)
);

CREATE TABLE Diagnostico (
    codigo_cie10 VARCHAR(10) PRIMARY KEY,
    descripcion_cie10 TEXT NOT NULL
);

CREATE TABLE Atencion_Diagnostico (
    n_atencion INT,
    codigo_cie10 VARCHAR(10),
    PRIMARY KEY (n_atencion, codigo_cie10),
    FOREIGN KEY (n_atencion) REFERENCES Atencion(n_atencion) ON DELETE CASCADE,
    FOREIGN KEY (codigo_cie10) REFERENCES Diagnostico(codigo_cie10)
);

CREATE TABLE Receta (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    n_atencion INT NOT NULL,
    medicamento VARCHAR(100) NOT NULL,
    dosis VARCHAR(50) NOT NULL,
    dias_tratamiento INT NOT NULL,
    FOREIGN KEY (n_atencion) REFERENCES Atencion(n_atencion) ON DELETE CASCADE
);

-- REQUISITOS OBLIGATORIOS (VIEW, FUNCTION, TRIGGER, PROCEDURE)

--  VIEW: PANEL ADMINISTRADOR 
CREATE VIEW Vista_Panel_Gestion AS
SELECT 
    cm.nombre_centro,
    COUNT(c.n_cita) AS total_citas,
    ROUND((SUM(CASE WHEN ec.nombre_estado = 'No Asistió' THEN 1 ELSE 0 END) / COUNT(c.n_cita)) * 100, 2) AS porcentaje_inasistencia
FROM Centro_Medico cm
LEFT JOIN Cita c ON cm.codigo_centro = c.codigo_centro
LEFT JOIN Estado_Cita ec ON c.id_estado = ec.id_estado
GROUP BY cm.nombre_centro;

-- FUNCTION SQL, COMPRUEBA TOPE DE HORARIO
DELIMITER //
CREATE FUNCTION FN_Tope_Horario(p_fecha DATE, p_hora TIME, p_rut_paciente VARCHAR(10), p_rut_medico VARCHAR(10)) 
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE v_existe INT;
    -- Revisa si el paciente o el médico ya tienen cita a esa hora y día
    SELECT COUNT(*) INTO v_existe 
    FROM Cita 
    WHERE fecha_cita = p_fecha 
    AND hora_cita = p_hora 
    AND (rut_paciente = p_rut_paciente OR rut_medico = p_rut_medico)
    AND id_estado IN (1, 2); --1 Reservada, 2: Confirmada
    
    RETURN v_existe > 0;
END //
DELIMITER ;

--PACIENTE INACTIVO TRIGGER
DELIMITER //
CREATE TRIGGER TR_Validar_Paciente_Activo
BEFORE INSERT ON Cita
FOR EACH ROW
BEGIN
    DECLARE v_count INT;
    SELECT COUNT(*) INTO v_count FROM Paciente WHERE rut_paciente = NEW.rut_paciente;
    IF v_count = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El paciente no existe o eliminó su cuenta.';
    END IF;
END //
DELIMITER ;

--AGENGAR CITA
DELIMITER //
CREATE PROCEDURE SP_Agendar_Cita(
    IN p_fecha DATE,
    IN p_hora TIME,
    IN p_rut_paciente VARCHAR(10),
    IN p_rut_medico VARCHAR(10),
    IN p_centro INT,
    IN p_especialidad INT
)
BEGIN
    
    IF FN_Tope_Horario(p_fecha, p_hora, p_rut_paciente, p_rut_medico) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Existe tope de horario para el paciente o médico.';
    ELSE
        INSERT INTO Cita (fecha_cita, hora_cita, id_estado, rut_paciente, rut_medico, codigo_centro, id_especialidad)
        VALUES (p_fecha, p_hora, 1, p_rut_paciente, p_rut_medico, p_centro, p_especialidad); -- 1: Reservada
    END IF;
END //
DELIMITER ;

**DATOS REQUERIDOS**

INSERT INTO Prevision (nombre_prevision) VALUES ('Fonasa'), ('Isapre'), ('Particular');
INSERT INTO Estado_Cita (nombre_estado) VALUES ('Reservada'), ('Confirmada'), ('Atendida'), ('No Asistió'), ('Cancelada');
INSERT INTO Especialidad (nombre_especialidad) VALUES ('Medicina General'), ('Cardiología'), ('Pediatría');

INSERT INTO Centro_Medico (nombre_centro, comuna, region) VALUES ('Centro Médico USM', 'Valparaíso', 'Valparaíso'), ('Clínica Viña', 'Viña del Mar', 'Valparaíso');

-- Claves de prueba  (123)
INSERT INTO Paciente (rut_paciente, nombre_paciente, id_prevision, telefono, fecha_nacimiento, sexo, clave) 
VALUES ('11111111-1', 'Juan Perez', 1, '912345678', '1990-01-01', 'M', '123');

INSERT INTO Medico (rut_medico, nombre_medico, email_medico, clave) 
VALUES ('22222222-2', 'Dra. Ana Gomez', 'ana.gomez@saludusm.cl', '123');

INSERT INTO Medico_Especialidad (rut_medico, id_especialidad) VALUES ('22222222-2', 1), ('22222222-2', 2);
INSERT INTO Medico_Centro (rut_medico, codigo_centro) VALUES ('22222222-2', 1), ('22222222-2', 2);

-- Más de 5 diagnósticos para probar el top 5 del panel de gestión
INSERT INTO Diagnostico (codigo_cie10, descripcion_cie10) VALUES 
('J00', 'Resfriado común'), ('I10', 'Hipertensión esencial'), ('E11', 'Diabetes mellitus tipo 2'), 
('J45', 'Asma'), ('K21', 'Reflujo gastroesofágico'), ('M54', 'Dorsalgia');
