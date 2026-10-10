-- Adaptado de PostgreSQL (Tarea 1) a MySQL (Tarea 2)

CREATE DATABASE IF NOT EXISTS saludusm;
USE saludusm;

CREATE TABLE Prevision (
    id_prevision INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE Estado_Cita (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE Especialidad (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE Centro_Medico (
    id_centro INT AUTO_INCREMENT PRIMARY KEY,
    codigo_interno VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    comuna VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL
);

CREATE TABLE Paciente (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    rut VARCHAR(12) UNIQUE NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    fecha_nac DATE NOT NULL,
    sexo VARCHAR(10) NOT NULL CHECK (sexo IN ('M', 'F', 'Otro')),
    id_prevision INT NOT NULL,
    telefono VARCHAR(20),
    comuna VARCHAR(100) NOT NULL,
    clave VARCHAR(255) NOT NULL DEFAULT '123', -- CAMPO AÑADIDO PARA LOGIN (TAREA 2)
    FOREIGN KEY (id_prevision) REFERENCES Prevision(id_prevision)
);

CREATE TABLE Medico (
    id_medico INT AUTO_INCREMENT PRIMARY KEY,
    rut VARCHAR(12) UNIQUE NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL DEFAULT '123' -- CAMPO AÑADIDO PARA LOGIN (TAREA 2)
);

CREATE TABLE Medico_Especialidad (
    id_medico INT NOT NULL,
    id_especialidad INT NOT NULL,
    PRIMARY KEY (id_medico, id_especialidad),
    FOREIGN KEY (id_medico) REFERENCES Medico(id_medico) ON DELETE CASCADE,
    FOREIGN KEY (id_especialidad) REFERENCES Especialidad(id_especialidad) ON DELETE CASCADE
);

CREATE TABLE Medico_Centro (
    id_medico INT NOT NULL,
    id_centro INT NOT NULL,
    PRIMARY KEY (id_medico, id_centro),
    FOREIGN KEY (id_medico) REFERENCES Medico(id_medico) ON DELETE CASCADE,
    FOREIGN KEY (id_centro) REFERENCES Centro_Medico(id_centro) ON DELETE CASCADE
);

CREATE TABLE Cita (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL,
    id_paciente INT NOT NULL,
    id_medico INT NOT NULL,
    id_centro INT NOT NULL,
    id_especialidad INT NOT NULL,
    id_estado INT NOT NULL,
    FOREIGN KEY (id_paciente) REFERENCES Paciente(id_paciente),
    FOREIGN KEY (id_medico) REFERENCES Medico(id_medico),
    FOREIGN KEY (id_centro) REFERENCES Centro_Medico(id_centro),
    FOREIGN KEY (id_especialidad) REFERENCES Especialidad(id_especialidad),
    FOREIGN KEY (id_estado) REFERENCES Estado_Cita(id_estado),
    CONSTRAINT uq_medico_centro_fecha UNIQUE (id_medico, id_centro, fecha_hora),
    CONSTRAINT uq_paciente_fecha UNIQUE (id_paciente, fecha_hora)
);

CREATE TABLE Diagnostico (
    codigo_cie VARCHAR(10) PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL
);

CREATE TABLE Atencion (
    id_cita INT PRIMARY KEY,
    motivo_consulta TEXT NOT NULL,
    observaciones TEXT NOT NULL,
    FOREIGN KEY (id_cita) REFERENCES Cita(id_cita) ON DELETE CASCADE
);

CREATE TABLE Atencion_Diagnostico (
    id_cita INT NOT NULL,
    codigo_cie VARCHAR(10) NOT NULL,
    PRIMARY KEY (id_cita, codigo_cie),
    FOREIGN KEY (id_cita) REFERENCES Atencion(id_cita) ON DELETE CASCADE,
    FOREIGN KEY (codigo_cie) REFERENCES Diagnostico(codigo_cie) ON DELETE CASCADE
);

CREATE TABLE Receta (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_cita INT NOT NULL,
    medicamento VARCHAR(150) NOT NULL,
    dosis VARCHAR(150) NOT NULL,
    dias_tratamiento INT NOT NULL CHECK (dias_tratamiento > 0),
    FOREIGN KEY (id_cita) REFERENCES Atencion(id_cita) ON DELETE CASCADE
);

--  (VIEW, FUNCTION, TRIGGER, PROCEDURE)

-- VIEW: Panel_Gestion (Administrador)
CREATE VIEW Vista_Panel_Gestion AS
SELECT 
    cm.nombre AS nombre_centro,
    COUNT(c.id_cita) AS total_citas,
    ROUND(IFNULL((SUM(CASE WHEN ec.nombre = 'No Asistió' THEN 1 ELSE 0 END) / COUNT(c.id_cita)) * 100, 0), 2) AS porcentaje_inasistencia
FROM Centro_Medico cm
LEFT JOIN Cita c ON cm.id_centro = c.id_centro
LEFT JOIN Estado_Cita ec ON c.id_estado = ec.id_estado
GROUP BY cm.nombre;

-- FUNCTION SQL: Verificar tope de horario
DELIMITER //
CREATE FUNCTION FN_Tope_Horario(p_fecha_hora DATETIME, p_id_paciente INT, p_id_medico INT) 
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE v_existe INT;
    SELECT COUNT(*) INTO v_existe 
    FROM Cita 
    WHERE fecha_hora = p_fecha_hora 
    AND (id_paciente = p_id_paciente OR id_medico = p_id_medico)
    AND id_estado IN (1, 2); 
    
    RETURN v_existe > 0;
END //
DELIMITER ;

-- TRIGGER: Validar fecha al agendar
DELIMITER //
CREATE TRIGGER TR_Validar_Fecha_Cita
BEFORE INSERT ON Cita
FOR EACH ROW
BEGIN
    IF NEW.fecha_hora < NOW() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede agendar una cita en el pasado.';
    END IF;
END //
DELIMITER ;

--  PROCEDIMIENTO ALMACENADO: Agendar Cita
DELIMITER //
CREATE PROCEDURE SP_Agendar_Cita(
    IN p_fecha_hora DATETIME,
    IN p_id_paciente INT,
    IN p_id_medico INT,
    IN p_id_centro INT,
    IN p_id_especialidad INT
)
BEGIN
    IF FN_Tope_Horario(p_fecha_hora, p_id_paciente, p_id_medico) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Existe tope de horario para el paciente o médico.';
    ELSE
        INSERT INTO Cita (fecha_hora, id_paciente, id_medico, id_centro, id_especialidad, id_estado)
        VALUES (p_fecha_hora, p_id_paciente, p_id_medico, p_id_centro, p_id_especialidad, 1);
    END IF;
END //
DELIMITER ;

-- DATOS DE PRUEBA 

INSERT INTO Prevision (nombre) VALUES ('Fonasa'), ('Isapre'), ('Particular');
INSERT INTO Estado_Cita (nombre) VALUES ('Reservada'), ('Confirmada'), ('Atendida'), ('No Asistió'), ('Cancelada');
INSERT INTO Especialidad (nombre) VALUES ('Medicina General'), ('Pediatría'), ('Cardiología'), ('Traumatología');

INSERT INTO Centro_Medico (codigo_interno, nombre, comuna, region) VALUES
('CM-VALP-01', 'Centro Médico USM Valparaíso', 'Valparaíso', 'Valparaíso'),
('CM-VINA-02', 'Clínica USM Viña del Mar', 'Viña del Mar', 'Valparaíso');

INSERT INTO Medico (rut, nombre_completo, email, clave) VALUES
('11111111-1', 'Dr. Carlos Mendoza Silva', 'carlos.mendoza@saludusm.cl', '123'),
('22222222-2', 'Dra. Ana Valenzuela Soto', 'ana.valenzuela@saludusm.cl', '123');

INSERT INTO Medico_Especialidad (id_medico, id_especialidad) VALUES (1, 1), (1, 3), (2, 2);
INSERT INTO Medico_Centro (id_medico, id_centro) VALUES (1, 1), (1, 2), (2, 1);

INSERT INTO Paciente (rut, nombre_completo, fecha_nac, sexo, id_prevision, telefono, comuna, clave) VALUES
('17000001-1', 'Matías Tapia', '1995-04-12', 'M', 2, '+56911112222', 'Valparaíso', '123'),
('17000002-2', 'Camila Rojas', '1982-11-23', 'F', 2, '+56911112223', 'Viña del Mar', '123');

INSERT INTO Diagnostico (codigo_cie, descripcion) VALUES
('J00', 'Rinofaringitis aguda (resfriado común)'), ('I10', 'Hipertensión esencial (primaria)'),
('E11', 'Diabetes mellitus tipo 2'), ('M54', 'Dorsalgia / Lumbalgia'), ('L20', 'Dermatitis atópica'), ('J06', 'Infección respiratoria aguda de vías superiores');
