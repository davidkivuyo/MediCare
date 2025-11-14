CREATE DATABASE IF NOT EXISTS `hospital_advanced` ;
USE `hospital_advanced`;

CREATE TABLE IF NOT EXISTS `doctors` (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_name` varchar(255) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `consultancy_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `doc_image` varchar(255) DEFAULT 'default.png',
  PRIMARY KEY (`doc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `doctors` (`doc_id`, `doc_name`, `specialization`, `email`, `consultancy_fees`, `doc_image`) VALUES
	(1, 'Dr.Joydeep', 'AI (Machine Neurology)', 'Ai@gmail.com', 500.00, 'https://images.pexels.com/photos/4167544/pexels-photo-4167544.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'),
	(2, 'Dr.Srinivas', 'DAA (Algorithmic Surgery)', 'daa@gmail.com', 750.00, 'https://images.pexels.com/photos/5215024/pexels-photo-5215024.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'),
	(3, 'Dr.Ramakrishna', 'DBMS (Database Cardiology)', 'dbms@gmail.com', 600.00, 'https://images.pexels.com/photos/5452293/pexels-photo-5452293.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'),
	(4, 'Dr.Sunil Das', 'Math (General Practice)', 'math@gmail.com', 400.00, 'https://images.pexels.com/photos/4173251/pexels-photo-4173251.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'),
	(5, 'Dr.Laksmi', 'OOPS (Systemic Pulmonology)', 'oops@gmail.com', 550.00, 'https://images.pexels.com/photos/5407206/pexels-photo-5407206.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'),
	(6, 'Dr.Newton', 'Physics (Radiology)', 'gravity@gmail.com', 800.00, 'https://images.pexels.com/photos/5797991/pexels-photo-5797991.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');

CREATE TABLE IF NOT EXISTS `patients` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  PRIMARY KEY (`pid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE IF NOT EXISTS `appointments` (
  `app_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `app_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`app_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`pid`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE IF NOT EXISTS `payments` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid') NOT NULL DEFAULT 'Pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`pay_id`),
  KEY `app_id` (`app_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `appointments` (`app_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `after_appointment_insert` AFTER INSERT ON `appointments` FOR EACH ROW BEGIN
    DECLARE fee DECIMAL(10, 2);
    SELECT consultancy_fees INTO fee FROM doctors WHERE doc_id = NEW.doctor_id;
    INSERT INTO payments (app_id, amount, payment_status)
    VALUES (NEW.app_id, fee, 'Pending');
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `after_doctor_update` AFTER UPDATE ON `doctors` FOR EACH ROW BEGIN
    IF NEW.consultancy_fees != OLD.consultancy_fees THEN
        UPDATE payments p
        JOIN appointments a ON p.app_id = a.app_id
        SET p.amount = NEW.consultancy_fees
        WHERE a.doctor_id = NEW.doc_id AND p.payment_status = 'Pending';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;