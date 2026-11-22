# SG-PROP - Modulo Funcional de Finanzas (Cobros y Pagos)

Sistema de Gestion de Propiedad Horizontal y Mantenimiento Operativo (SG-PROP).
Este modulo implementa la gestion de unidades inmobiliarias, emision masiva de avisos de cobro por alicuota y conciliacion de reportes de pago.

## Requisitos previos

- PHP 8.2 o superior (con extensiones `mbstring` y `json`).
- MariaDB / MySQL (para persistencia en integracion completa).
- Git y cuenta en GitHub con acceso al repositorio.
- Opcional para ejecucion local de calidad/pruebas: PHPUnit y PHP_CodeSniffer (`phpcs` bajo PSR-12).

## Instalacion y ejecucion

1. Clonar el repositorio:
   ```bash
   git clone [https://github.com/alejajer/sg-prop-modulo-funcional.git](https://github.com/alejajer/sg-prop-modulo-funcional.git)
   cd sg-prop-modulo-funcional
   
- Ejecutar pruebas de verificacion: \php tests/FinanzasTest.php`y haz commit con el mensaje:docs(readme): agregar comando de ejecucion de pruebas unitarias`
