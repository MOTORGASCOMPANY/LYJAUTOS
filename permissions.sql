-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 28-05-2026 a las 21:45:33
-- Versión del servidor: 8.0.45-0ubuntu0.24.04.1
-- Versión de PHP: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mtg`
--

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `descripcion`, `guard_name`, `created_at`, `updated_at`) VALUES
(4, 'expedientes', 'Listar crear y editar expedientes', 'web', '2022-12-01 20:51:44', '2023-05-10 08:54:52'),
(5, 'revisionExpedientes', 'Revisar expedientes', 'web', '2022-12-01 20:51:44', '2023-05-10 08:54:40'),
(6, 'ingresos', 'Listar y crear ingresos de materiales', 'web', '2023-01-24 22:41:53', '2023-05-10 08:54:28'),
(7, 'salidas', 'Listar salidas de materiales', 'web', '2023-01-24 22:41:53', '2023-05-10 08:54:09'),
(8, 'asignacion', 'Asignar materiales a un inspector', 'web', '2023-01-24 22:41:53', '2023-05-10 08:53:54'),
(9, 'inventario', 'Ver inventarios', 'web', '2023-01-24 22:41:53', '2023-05-10 08:53:38'),
(10, 'servicio', 'Crear servicios', 'web', '2023-01-24 22:41:53', '2023-05-10 08:53:24'),
(11, 'recepcion', 'Recepcionar materiales', 'web', '2023-01-24 22:41:53', '2023-05-10 08:53:16'),
(12, 'solicitud', 'Listar y ver solicitudes de materiales', 'web', '2023-01-24 22:41:53', '2023-05-10 08:53:02'),
(13, 'nuevaSolicitud', 'Crear solicitudes de materiales', 'web', '2023-01-24 22:41:53', '2023-05-10 08:52:39'),
(14, 'certificaciones', NULL, 'web', '2023-02-09 13:03:56', '2023-02-09 13:03:56'),
(15, 'talleres', 'Listar, agregar y editar talleres', 'web', '2023-02-18 12:29:10', '2023-05-06 10:40:25'),
(16, 'admin.certificaciones', 'Administrar servicios realizados(Eliminar y anular)', 'web', '2023-02-18 12:29:10', '2023-05-06 10:39:53'),
(17, 'talleres.revision', 'Revisar expedientes y servicios (POR TALLER)', 'web', '2023-04-10 16:23:02', '2023-05-06 10:39:23'),
(18, 'editar-taller', 'Editar datos y documentos de taller', 'web', '2023-04-22 09:35:32', '2023-05-06 10:38:56'),
(19, 'inventario.revision', 'Revisar inventarios de inspectores', 'web', '2023-05-05 16:50:10', '2023-05-05 16:50:48'),
(20, 'materiales.prestamo', 'Préstamo de materiales entre inspectores', 'web', '2023-05-16 17:58:47', '2023-05-16 17:58:47'),
(21, 'certificaciones.pendientes', 'Lista la certificaciones pendientes que tenga cada inspector', 'web', '2023-05-23 09:34:11', '2023-05-23 09:34:11'),
(22, 'opciones.usuarios', 'Puede ver en el menú de navegación las opciones del modulo de usuarios', 'web', '2023-05-24 11:14:57', '2023-05-24 11:14:57'),
(23, 'opciones.servicios', 'Puede ver en el menú de navegación las opciones del modulo de servicios', 'web', '2023-05-24 11:27:34', '2023-05-24 11:27:34'),
(24, 'opciones.expedientes', 'Puede ver en el menú de navegación las opciones del modulo de expedientes', 'web', '2023-05-24 11:27:46', '2023-05-24 11:27:46'),
(25, 'opciones.talleres', 'Puede ver en el menú de navegación las opciones del modulo de talleres', 'web', '2023-05-24 11:28:01', '2023-05-24 11:28:01'),
(26, 'opciones.materiales', 'Puede ver en el menú de navegación las opciones del modulo de material', 'web', '2023-05-24 11:28:20', '2023-05-24 11:28:20'),
(27, 'usuarios', 'Administrar usuarios', 'web', '2023-05-24 11:35:21', '2023-05-24 11:35:21'),
(28, 'usuarios.roles', 'Administrar roles de usuario', 'web', '2023-05-24 11:35:45', '2023-05-24 11:35:50'),
(29, 'usuarios.permisos', 'Administrar permisos de rol', 'web', '2023-05-24 11:36:09', '2023-05-24 11:36:09'),
(30, 'importar.anuales', 'Importar Excel de Servicios de Revisión Anual desde los reportes del sistema Gasolution.', 'web', '2023-06-09 17:14:54', '2023-06-09 17:14:54'),
(31, 'opciones.reportesGnv', 'Puede ver en el menú de navegación las opciones de reportes GNV', 'web', '2023-06-20 20:29:46', '2023-06-20 20:29:46'),
(32, 'opciones.cargaDatos', 'Puede ver en el menú de navegación las opciones del modulo de carga de datos', 'web', '2023-06-20 20:30:02', '2023-06-20 20:30:02'),
(33, 'reportes.reporteGeneralGnv', 'Puede ver el reporte general de Gnv', 'web', '2023-06-20 20:30:17', '2023-06-22 08:53:19'),
(34, 'reportes.reporteMateriales', 'Puede ver el reporte de formatos GNV', 'web', '2023-06-20 20:30:48', '2023-06-20 20:30:48'),
(35, 'reportes.reporteServiciosPorInspector', 'Permite ver el reporte de servicios por Inspector', 'web', '2023-06-23 14:59:32', '2023-06-23 14:59:32'),
(36, 'reportes.reporteDocumentosTaller', 'Permite ver que documentos están por vencer de los talleres', 'web', '2023-11-16 16:08:01', '2023-11-16 16:08:01'),
(37, 'reportes.reporteFotosPorInspector', 'Permiso para ver que inspectores están subiendo foto a sus expedientes.', 'web', '2023-11-16 16:08:20', '2023-11-16 16:08:20'),
(38, 'ConsultarHoja', 'Permite ver estado de la hoja', 'web', '2023-11-16 16:34:41', '2023-11-16 16:34:41'),
(39, 'ServicioModi', 'Crea Servicios Modificación', 'web', '2023-12-12 11:48:56', '2023-12-12 11:48:56'),
(40, 'ListadoChips', 'Lista de chips ', 'web', '2023-12-29 10:27:00', '2023-12-29 10:27:00'),
(41, 'reportes.reporteCalcularGasol', 'Puede ver el reporte general de Gnv Gasolution', 'web', '2024-01-22 16:29:00', '2024-01-22 16:29:00'),
(42, 'reportes.reporteCalcular', 'Puede ver el reporte general de MTC', 'web', '2024-01-22 16:29:17', '2024-01-22 16:29:17'),
(43, 'opciones.nosotros', 'Puede ver en el menú de navegación las opciones del modulo de nosotros', 'web', '2024-03-12 08:48:54', '2024-03-12 08:48:54'),
(44, 'reportes.reporteActualizarPrecio', 'Permite actualizar los precios de las certificaciones', 'web', '2024-03-20 10:53:34', '2024-03-20 10:53:34'),
(45, 'ManualFunciones', 'Permite cargar los documentos del manual de funciones', 'web', '2024-03-25 16:33:56', '2024-03-25 16:33:56'),
(46, 'Memorando', 'Permite realizar los memorandos', 'web', '2024-03-25 16:34:09', '2024-03-25 16:34:09'),
(47, 'ListaMemorando', 'Permite ver la lista de Memorándums', 'web', '2024-03-25 16:34:24', '2024-03-25 16:34:24'),
(48, 'certificaciones.desmontes', 'Listado de otras certificaciones ', 'web', '2024-03-28 10:27:11', '2024-07-25 16:34:43'),
(49, 'PreciosInspector', 'Permite colocar los precios a los inspectores dependiendo su tipo de servicio ', 'web', '2024-04-13 12:09:01', '2024-04-13 12:09:01'),
(50, 'Empleados', 'Permite ver y crear los empleados', 'web', '2024-04-18 18:18:18', '2024-04-18 18:18:18'),
(51, 'reportes.reporteMTG', 'Permite ver el reporte detallado', 'web', '2024-04-26 16:15:57', '2024-04-26 16:15:57'),
(52, 'reportes.reporteCalcularChip', 'Permite ver el reporte de los talleres', 'web', '2024-04-30 17:25:46', '2024-04-30 17:25:46'),
(53, 'reportes.reporteActualizarGasol', 'Permite actualizar los precios de las certificaciones de gasolution', 'web', '2024-05-25 11:44:59', '2024-05-25 11:44:59'),
(54, 'reportes.reporteGasol', 'Reporte detallado de Gasolution', 'web', '2024-05-25 11:45:16', '2024-05-25 11:45:16'),
(55, 'opciones.reportesCertificaciones', 'Puede ver en el menú de navegación las opciones de reportes de certificaciones', 'web', '2024-05-28 16:56:12', '2024-05-28 16:56:12'),
(56, 'opciones.reportesMaterial', 'Puede ver en el menú de navegación las opciones de reportes de materiales', 'web', '2024-06-06 09:52:09', '2024-06-06 09:52:09'),
(57, 'reportes.reporteSalidaMateriales', 'Puede ver el reporte de salida de materiales', 'web', '2024-06-06 09:52:34', '2024-06-06 09:52:34'),
(58, 'comunicado.createOrUpdateForm', 'Puede emitir comunicados', 'web', '2024-06-12 14:42:28', '2024-06-13 15:55:20'),
(59, 'reportes.reporteInventario', 'Puede ver el reporte de inventarios del inspector', 'web', '2024-06-20 17:27:10', '2024-06-20 17:27:10'),
(60, 'Rentabilidad', 'Puede ver la rentabilidad de los talleres', 'web', '2024-07-01 16:26:56', '2024-07-01 16:26:56'),
(61, 'ServicioTaller', 'Crea Certificaciones de Inspección de Taller', 'web', '2024-07-03 16:35:37', '2024-07-03 16:35:37'),
(62, 'AdministracionCerTaller', 'Administración de otras certificaciones', 'web', '2024-07-03 16:35:55', '2024-07-25 16:31:36'),
(63, 'ListaBoletas', 'Importar y subir comprobantes y estados de cuenta', 'web', '2024-07-13 12:02:31', '2024-07-13 12:02:31'),
(64, 'reportes.reporteTallerResumen', 'Puede ver el reporte de taller resumen', 'web', '2024-08-27 11:01:41', '2024-09-10 11:37:07'),
(65, 'ServicioCarta', 'Crea Certificaciones de Cartas Aclaratorias', 'web', '2024-10-22 17:00:09', '2024-10-22 17:00:09'),
(66, 'TalleresInspector', 'Para establecer relacion entre talleres e inspectores para rpta taller resumen', 'web', '2024-10-22 17:00:30', '2024-10-22 17:00:30'),
(67, 'reportes.reporteExternosMTG', 'Puede ver el reporte resumen de externos', 'web', '2024-11-11 08:45:38', '2024-11-11 08:45:38'),
(68, 'reportes.resumenDeben', 'Reporte para ver todos los externos y talleres que tienen deuda', 'web', '2025-01-07 14:43:56', '2025-01-07 14:43:56'),
(69, 'Candado', 'Candado para las anulaciones y eliminaciones.', 'web', '2025-01-11 10:06:11', '2025-01-11 10:06:11'),
(70, 'reportes.resumenActualizar', 'Para actualizar las deudas (pagados)', 'web', '2025-02-01 11:42:44', '2025-02-01 11:42:44'),
(71, 'reportes.fechaActualizar', 'Para poder actualizar fecha a las certificaciones', 'web', '2025-02-01 11:42:57', '2025-02-01 11:42:57'),
(72, 'reportes.resumenDeudas', 'Reporte para ver las deudas de boletas de externos y talleres', 'web', '2025-02-18 15:53:43', '2025-02-18 15:53:43'),
(73, 'opciones.materialesContado', 'Puede ver en el menú las opciones del modulo de materiales al contado', 'web', '2025-02-19 16:26:15', '2025-02-19 16:26:15'),
(74, 'reportes.reporteDocumentosEmpleados', 'Permite ver documentos que faltan a los empleados', 'web', '2025-03-08 11:32:50', '2025-03-08 11:32:50'),
(75, 'Controlformatos', 'Control de formatos según el grupo', 'web', '2025-06-14 12:11:26', '2025-06-14 12:11:26'),
(76, 'reportes.externoActualizar', 'Para poder actualizar externo a las certificaciones', 'web', '2025-07-24 12:47:26', '2025-07-24 12:47:26'),
(77, 'servicioTemporal', 'Servicio temporales para control glp con otra certificadora', 'web', '2025-08-22 10:05:58', '2025-08-22 10:05:58'),
(78, 'Planillas', 'Listar y crear planillas según el periodo', 'web', '2025-09-17 14:53:19', '2025-09-17 14:53:19'),
(79, 'Gratificaciones', 'Listar y crear gratificaciones según el periodo', 'web', '2025-12-13 11:57:46', '2025-12-13 11:57:46'),
(80, 'GastosAdministrativos', 'Registrar gastos administrativos de forma mensual', 'web', '2025-12-24 11:30:02', '2025-12-24 11:30:02'),
(81, 'RentabilidadResumen', 'Puede ver la rentabilidad resumen completo', 'web', '2026-01-16 10:04:08', '2026-01-16 10:04:08'),
(82, 'opciones.contabilidad', 'Puede ver en el menú las opciones del modulo de contabilidad', 'web', '2026-01-16 10:04:24', '2026-01-16 10:04:24'),
(83, 'opciones.asistencia', 'Puede ver en el menú de navegación las opciones de control de asistencia', 'web', '2026-02-06 10:43:15', '2026-02-06 10:43:15'),
(84, 'autorizarDispositivo', 'Autorizar estación de trabajo para control de asistencias', 'web', '2026-02-06 10:43:34', '2026-02-06 10:43:34'),
(85, 'horarioEmpleados', 'Gestión de horarios para control de asistencia', 'web', '2026-02-06 10:43:55', '2026-02-06 10:43:55'),
(86, 'AsignarHorario', 'Vincular horario a personal o usuario', 'web', '2026-02-06 10:44:12', '2026-02-06 10:44:12'),
(87, 'dashboardAsistencia', 'Monitor en tiempo real para registro de marcaciones', 'web', '2026-02-06 10:44:27', '2026-02-06 10:44:27'),
(88, 'reporteAsistencia', 'Reportes de Asistencias', 'web', '2026-02-06 10:44:41', '2026-02-06 10:44:41');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
