# EcoRuta

<p align="center">
  <strong>Sistema web para la administración y distribución ecológica de pedidos</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Estado-En%20desarrollo-yellow" alt="Estado">
  <img src="https://img.shields.io/badge/Backend-PHP-blue" alt="Backend">
  <img src="https://img.shields.io/badge/Frontend-HTML%20%7C%20CSS%20%7C%20JavaScript-orange" alt="Frontend">
  <img src="https://img.shields.io/badge/Framework-Bootstrap-purple" alt="Bootstrap">
  <img src="https://img.shields.io/badge/Base%20de%20datos-MySQL%20%7C%20MariaDB-informational" alt="Base de datos">
</p>

## Sobre el proyecto

**EcoRuta** es un sistema web orientado a la **administración y distribución ecológica de pedidos**, cuyo propósito es facilitar la gestión de entregas y promover una logística más eficiente y sostenible.

El sistema permite gestionar el ciclo de vida de los pedidos, desde su registro hasta su entrega, incorporando criterios relacionados con la **distancia, medio de transporte y eficiencia de las rutas** para calcular una tarifa ecológica.

EcoRuta está compuesto principalmente por dos interfaces:

* **Panel web:** destinado a administradores y comerciantes.
* **Interfaz adaptativa:** destinada a repartidores desde dispositivos móviles.

---

## Objetivo

Desarrollar una solución tecnológica que permita **gestionar y optimizar la distribución de pedidos**, incorporando criterios ecológicos que contribuyan a reducir recorridos innecesarios y fomentar métodos de entrega más sostenibles.

### Objetivos específicos

* Gestionar pedidos de forma digital.
* Administrar repartidores y asignar pedidos.
* Facilitar la planificación y visualización de rutas.
* Calcular tarifas considerando criterios ecológicos.
* Obtener información sobre las entregas realizadas.
* Mantener informados a los usuarios sobre el estado de sus pedidos.
* Generar indicadores relacionados con el impacto ambiental de las entregas.

---

# Funcionalidades

## Administración

El administrador podrá gestionar los principales elementos del sistema:

* Gestión de usuarios.
* Gestión de comerciantes.
* Gestión de clientes.
* Gestión de repartidores.
* Gestión de pedidos.
* Asignación de repartidores.
* Consulta del estado de las entregas.
* Configuración de criterios ecológicos.
* Visualización de estadísticas e indicadores.

---

## Comerciantes

Los comerciantes podrán:

* Registrar nuevos pedidos.
* Consultar sus pedidos.
* Consultar información de los clientes.
* Visualizar el estado de sus entregas.
* Consultar la tarifa ecológica.
* Revisar información básica de las entregas.

---

## Repartidores

La interfaz para repartidores estará adaptada principalmente para dispositivos móviles.

Permitirá:

* Consultar pedidos asignados.
* Visualizar información del pedido.
* Consultar la dirección de entrega.
* Visualizar la ruta.
* Consultar indicaciones de recorrido.
* Actualizar el estado del pedido.
* Recibir notificaciones.
* Confirmar la entrega.

---

# Sistema de tarifa ecológica

Una de las características principales de EcoRuta es la incorporación de criterios ambientales en el cálculo de las entregas.

Entre los factores que pueden ser considerados se encuentran:

* Distancia.
* Tipo de transporte.
* Cantidad de pedidos.
* Consumo estimado.
* Emisiones de CO₂.
* Eficiencia de la ruta.

---

# Tecnologías

### Frontend

| Tecnología | Uso                               |
| ---------- | --------------------------------- |
| HTML5      | Estructura de las páginas         |
| CSS3       | Estilos y diseño                  |
| JavaScript | Interactividad                    |
| Bootstrap  | Diseño responsive                 |
| DataTables | Gestión y visualización de tablas |

### Backend

| Tecnología | Uso                 |
| ---------- | ------------------- |
| PHP        | Lógica del servidor |

### Base de datos

| Tecnología | Uso                    |
| ---------- | ---------------------- |
| MySQL      | Gestión de datos       |

### Herramientas

* Visual Studio Code
* Git
* GitHub
* XAMPP
* phpMyAdmin

---
<!--
# Estructura del proyecto

```text
eco-ruta/
│
├── assets/
│   ├── css/
│   │   └── styles.css
│   │
│   ├── js/
│   │   └── app.js
│   │
│   ├── img/
│   │
│   └── libraries/
│       ├── bootstrap/
│       └── datatables/
│
├── config/
│   └── database.php
│
├── controllers/
│
├── models/
│
├── views/
│   ├── admin/
│   ├── comerciante/
│   ├── repartidor/
│   └── login/
│
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
│
├── database/
│   └── ecoruta.sql
│
├── index.php
├── .gitignore
└── README.md
```

---
-->
# Requisitos

Para ejecutar EcoRuta localmente se necesita:

* PHP 8.x o superior.
* MySQL.
* Apache.
* Navegador web actualizado.
* Git.
* XAMPP, WAMP o un entorno equivalente.

---

# Instalación

## 1. Clonar el repositorio

```bash
git clone https://github.com/EJ642/eco-ruta.git
```

## 2. Entrar al directorio

```bash
cd eco-ruta
```

## 3. Colocar el proyecto en el servidor

Si utilizas XAMPP:

```text
C:\xampp\htdocs\eco-ruta
```

## 4. Crear la base de datos

Desde **phpMyAdmin** o MySQL:

1. Crear una base de datos llamada `ecoruta_db`.
2. Importar el archivo:

```text
database/ecoruta_db.sql
```

## 5. Configurar la conexión

Editar:

```text
servicios/conexion.php
```

Ejemplo:

```php
<?php

$host = "localhost";
$database = "ecoruta_db";
$user = "root";
$password = "";

$conexion = new mysqli(
    $host,
    $user,
    $password,
    $database
);
```

> Los datos de conexión deben adaptarse a la configuración del entorno local.

## 6. Iniciar los servicios

Desde XAMPP iniciar:

```text
Apache
MySQL
```

## 7. Abrir EcoRuta

Ingresar desde el navegador:

```text
http://localhost/eco-ruta/
```

---
<!--
# 🔐 Seguridad

El sistema contempla la utilización de diferentes roles para controlar el acceso a las funcionalidades.

```text
                    USUARIO
                       │
                       ▼
                  AUTENTICACIÓN
                       │
                       ▼
                 ┌─────┴─────┐
                 │           │
                 ▼           ▼
            ADMINISTRADOR  COMERCIANTE
                 │
                 │
                 ▼
             REPARTIDOR
```

Cada rol tendrá acceso únicamente a las funcionalidades correspondientes a sus responsabilidades dentro del sistema.
-->
<!-- 
# Indicadores ambientales

EcoRuta puede utilizar la información generada por las entregas para construir indicadores como:

* 🚴 Cantidad de entregas realizadas.
* 🗺️ Distancia total recorrida.
* 📍 Distancia promedio por entrega.
* 🌱 Medio de transporte utilizado.
* 💨 Emisiones estimadas de CO₂.
* 📉 Reducción potencial de emisiones.
* ⚡ Eficiencia de las rutas.

Estos indicadores permitirán evaluar el desempeño logístico y ambiental del sistema.

---

# 📱 Diseño responsive

La aplicación está pensada para adaptarse a diferentes dispositivos:

```text
┌─────────────────┐
│                 │
│    DESKTOP      │
│                 │
│  Administración │
│  Comerciantes   │
│                 │
└─────────────────┘


        ┌─────────┐
        │         │
        │ MÓVIL   │
        │         │
        │Repartidor│
        │         │
        └─────────┘
```

El panel administrativo está orientado principalmente a computadoras, mientras que la interfaz de los repartidores prioriza la utilización desde smartphones.

---
-->
# Futuras funcionalidades

El proyecto contempla la incorporación progresiva de nuevas funcionalidades:

* [ ] Autenticación y autorización por roles.
* [ ] ABMC de usuarios.
* [ ] ABMC de comerciantes.
* [ ] ABMC de clientes.
* [ ] ABMC de repartidores.
* [ ] Gestión de pedidos.
* [ ] Asignación automática de repartidores.
* [ ] Cálculo de tarifas ecológicas.
* [ ] Integración con mapas.
* [ ] Visualización de rutas.
* [ ] Optimización de rutas.
* [ ] Geolocalización de repartidores.
* [ ] Notificaciones.
* [ ] Confirmación digital de entregas.
* [ ] Dashboard administrativo.
* [ ] Estadísticas de entregas.
* [ ] Indicadores ambientales.
* [ ] Cálculo estimado de emisiones de CO₂.

---

# Estado del proyecto

 **EcoRuta se encuentra actualmente en desarrollo.**

El proyecto está siendo desarrollado de forma progresiva, incorporando primero las funcionalidades principales de administración de pedidos y posteriormente las herramientas relacionadas con rutas, repartición e indicadores ecológicos.

---

# Licencia

Este proyecto se encuentra actualmente en desarrollo con fines **académicos y educativos**.

La licencia definitiva del proyecto será establecida posteriormente.

---

# Autores

**Sergio Daniel Aquino, Álvaro Ortega, Elías Huerta, Thaiel Duarte y Paola Oviedo**

Proyecto: **EcoRuta**

Sistema web para la administración y distribución ecológica de pedidos.

---

<p align="center">

**EcoRuta**

**Tecnología para una distribución más eficiente y sostenible.**

</p>