# Guía de despliegue paso a paso · rosaacevedo.com en Hostinger

Para quien no es experto en desarrollo y quiere entender lo que hace, no solo seguir instrucciones. Cada paso dice **qué** vas a hacer, **por qué** y **cómo saber que salió bien**. Hazlos en orden. Si algo no se ve igual, toma una captura y compártela: los menús de Hostinger cambian de nombre con el tiempo, pero las ideas no.

> Tiempo estimado: 1 a 2 horas la primera vez. Después, publicar un cambio toma un clic.

---

## Parte 0 · Entender el mapa

### Cómo viaja el sitio desde el código hasta la gente

```
 Tu Mac / Claude          GitHub                         Hostinger                      Visitantes
 (escriben código)  →  (guarda el código)  →  GitHub Actions lo prueba, lo arma  →  (servidor)  →  rosaacevedo.com
                                               y lo sube por SSH
```

1. El código vive en **GitHub**, en un **repositorio** (una carpeta con historial de cambios).
2. Cuando algo llega a la rama **`main`**, un robot de GitHub (**GitHub Actions**) corre las pruebas, arma el sitio y lo copia al servidor de **Hostinger**.
3. Hostinger lo publica en internet. La gente entra a **rosaacevedo.com**.

Tú no copias archivos a mano: configuras una vez el camino y luego el robot hace el viaje cada vez.

### Glosario mínimo

| Palabra | Qué es, en simple |
|---|---|
| **Repositorio** | La carpeta del proyecto en GitHub, con todo su historial |
| **Rama** | Una versión paralela del proyecto. `main` es la oficial; Claude trabaja en `ccr-edfbac35-1nj28z` |
| **Commit** | Un cambio guardado con su descripción |
| **Pull request (PR)** | Una solicitud para pasar los cambios de una rama a otra. Se revisan y se aceptan con «Merge» |
| **Merge** | Unir: los cambios pasan a `main` |
| **Servidor** | El computador de Hostinger, encendido siempre, que entrega el sitio |
| **Terminal** | La aplicación de tu Mac donde se escriben comandos (Aplicaciones → Utilidades → Terminal) |
| **SSH** | Una forma segura de entrar al servidor desde la Terminal |
| **Llave SSH** | Un par de archivos: la **pública** es como un candado que instalas en el servidor; la **privada** es la llave que lo abre. La privada nunca se comparte |
| **Secreto de GitHub** | Una clave guardada en GitHub que el robot puede usar sin que nadie la vea |
| **Base de datos** | Donde quedan los registros, propuestas, noticias y la configuración del panel |
| **`.env`** | El archivo de configuración privada del sitio en el servidor (contraseña de la base de datos y llaves de cifrado) |
| **Cron** | Una tarea programada: le dice al servidor «haz esto cada minuto» |
| **DNS** | El directorio de internet que dice a qué servidor lleva el nombre rosaacevedo.com |
| **SSL / HTTPS** | El candado del navegador: la conexión va cifrada |

### Lo que hace el robot por ti (y por qué)

En cada despliegue, GitHub Actions:

1. **Prueba el sitio** (las 70+ pruebas automáticas). Si alguna falla, no publica nada.
2. **Arma** el sitio: descarga las librerías y compila estilos e imágenes.
3. **Lo sube** a una carpeta nueva en el servidor (`releases/fecha-hora`), sin tocar la versión que está funcionando.
4. **La primera vez** prepara el servidor: crea las carpetas, crea el archivo `.env` con llaves de cifrado nuevas y conecta tu dominio con el sitio. Las llaves las genera el servidor, así nadie las ve ni se pueden copiar mal.
5. **Actualiza la base de datos** (las «migraciones») y, solo la primera vez, carga los datos base: comunas, ejes, temas, redes, roles, políticas y páginas.
6. **Crea tu usuario** del panel si todavía no hay ninguno.
7. **Cambia al instante** a la versión nueva. Si algo falla antes, el sitio sigue con la anterior.

---

## Parte 1 · Antes de empezar: reúne esto

- [ ] Acceso a **hPanel** de Hostinger con el plan que alojará el sitio. Debe permitir **SSH** y **tareas Cron**: los planes Business y Cloud lo permiten; el plan más básico a veces no.
- [ ] El dominio **rosaacevedo.com** agregado en ese plan de Hostinger.
- [ ] Ser **administrador** del repositorio en GitHub (para crear secretos).
- [ ] Un **gestor de contraseñas** (el llavero de iCloud, 1Password, Bitwarden) para guardar lo que vas a crear. Vas a manejar 4 contraseñas nuevas.

---

## Parte 2 · Hostinger

### Paso 2.1 · Elegir PHP 8.3

**Qué es:** PHP es el lenguaje en el que está escrito el sitio. Necesita la versión 8.3.
**Cómo:** hPanel → **Sitios web** → rosaacevedo.com → **Administrar** → busca **Configuración de PHP** (o «PHP Configuration») → versión **8.3** → Guardar.
**Extensiones:** en la misma pantalla, pestaña de extensiones, verifica que estén marcadas: `gd`, `intl`, `zip`, `exif`, `pdo_mysql`, `sodium`, `bcmath`. Normalmente ya vienen.
**Cómo saber que salió bien:** la pantalla muestra «PHP 8.3» como versión actual.

### Paso 2.2 · Crear la base de datos

**Qué es:** el lugar donde el sitio guarda todo lo que la gente envía y lo que el equipo configura.
**Cómo:** hPanel → **Bases de datos** → **Administración** (o «Bases de datos MySQL») → crear una nueva:
- Nombre: `rosa` → Hostinger le pone un prefijo y queda algo como **`u123456789_rosa`**.
- Usuario: `rosa` → queda como **`u123456789_rosa`**.
- Contraseña: usa el generador del gestor, larga (20+ caracteres). **Evita el apóstrofo `'`**.

**Anota en tu gestor** (los vas a necesitar en la Parte 4):
| Dato | Ejemplo |
|---|---|
| Nombre de la base | `u123456789_rosa` |
| Usuario de la base | `u123456789_rosa` |
| Contraseña de la base | `(la que generaste)` |

**Cómo saber que salió bien:** la base aparece en la lista de bases de datos.

### Paso 2.3 · Activar el acceso SSH

**Qué es:** abrir la puerta segura para que el robot de GitHub (y tú) puedan entrar al servidor.
**Cómo:** hPanel → **Avanzado** → **Acceso SSH** → **Activar**.
**Anota** lo que muestra la pantalla:
| Dato | Ejemplo |
|---|---|
| IP (o host) | `123.45.67.89` |
| Puerto | `65002` |
| Usuario | `u123456789` |

### Paso 2.4 · Crear la llave SSH en tu Mac

**Qué es:** el par candado/llave para entrar sin contraseña. Vas a crear uno solo para el despliegue.

1. Abre la **Terminal** (Aplicaciones → Utilidades → Terminal).
2. Escribe este comando y presiona Enter:
   ```bash
   ssh-keygen -t ed25519 -C "despliegue rosaacevedo" -f ~/.ssh/rosa_despliegue -N ""
   ```
   - `ssh-keygen` crea llaves; `-t ed25519` es el tipo (moderno y seguro).
   - `-f ~/.ssh/rosa_despliegue` es dónde se guarda: crea **dos archivos**, `rosa_despliegue` (privada) y `rosa_despliegue.pub` (pública).
   - `-N ""` significa «sin frase de contraseña», necesario para que el robot pueda usarla sola.
3. Copia la llave **pública** al portapapeles:
   ```bash
   pbcopy < ~/.ssh/rosa_despliegue.pub
   ```
   (`pbcopy` copia lo que le des al portapapeles del Mac. No muestra nada: es normal.)
4. En hPanel → **Avanzado** → **Acceso SSH** → **Claves SSH** (o «Administrar claves SSH») → **Agregar clave** → ponle un nombre (`despliegue`) → pega con Cmd+V → Guardar.

**Cómo saber que salió bien:** la clave aparece en la lista, empezando por `ssh-ed25519`.

### Paso 2.5 · Entrar tú al servidor (tu primera vez por SSH)

**Por qué:** para comprobar que la llave funciona y para que conozcas el servidor. Cambia los datos de ejemplo por los tuyos:

```bash
ssh -o IdentitiesOnly=yes -i ~/.ssh/rosa_despliegue -p 65002 u123456789@123.45.67.89
```
- `-i` dice qué llave usar, `-o IdentitiesOnly=yes` que use solo esa, `-p` el puerto, y luego `usuario@ip`.
- La primera vez pregunta «Are you sure you want to continue connecting?»: escribe `yes` y Enter.

Ya estás **dentro del servidor**. El texto antes del cursor cambia. Prueba estos comandos (uno a la vez):

```bash
pwd                                   # dónde estás: tu carpeta de inicio, algo como /home/u123456789
ls                                    # qué hay aquí; verás la carpeta domains
ls domains                            # tus dominios; debe aparecer rosaacevedo.com
/opt/alt/php83/usr/bin/php -v         # la versión de PHP 8.3 del servidor
exit                                  # salir del servidor y volver a tu Mac
```

**Cómo saber que salió bien:** entraste sin que te pidiera contraseña y `php -v` dice `PHP 8.3`.

**Si te pide una contraseña:** lee qué pide exactamente.
- `Enter passphrase for key '…rosa_despliegue':` → es la frase de la llave. Si la creaste con el comando de arriba (`-N ""`), no tiene: presiona Enter.
- `u123456789@…'s password:` → **el servidor rechazó la llave**. No escribas ahí la llave privada ni la clave de hPanel: no sirven. Haz esto:
  1. `cat ~/.ssh/rosa_despliegue.pub` y compara esa línea con la que está en hPanel → Acceso SSH → Claves SSH (inicio y final iguales). Si no está o no coincide, bórrala y agrégala de nuevo (paso 2.4). Puede tardar un par de minutos en activarse.
  2. Entra obligando a usar solo esa llave (tu Mac puede estar ofreciendo otras primero):
     ```bash
     ssh -o IdentitiesOnly=yes -i ~/.ssh/rosa_despliegue -p 65002 u123456789@123.45.67.89
     ```
  3. Verifica que usuario, IP y puerto sean exactamente los de hPanel.
  4. Si sigue igual, agrega `-v` al comando para ver el detalle y comparte las últimas líneas antes de la pregunta de contraseña (no contienen secretos).

  hPanel permite ponerle una contraseña al SSH, pero GitHub entra con la llave, así que la llave tiene que funcionar.

---

## Parte 3 · Inventar 4 datos y anotarlos (todavía no se escriben en ningún sistema)

**Qué es:** el despliegue va a crear tu usuario del panel y una «puerta con clave» temporal frente al sitio. Para eso necesita 4 datos que tú inventas ahora. **En este paso no entras a Hostinger ni a GitHub:** solo los inventas y los guardas. En la Parte 4 los pegas en GitHub.

**Qué hacer:** en tu gestor de contraseñas crea una nota «Rosa Acevedo · despliegue» con:

| # | Dato | Qué escribir | Para qué sirve |
|---|---|---|---|
| 1 | Correo del panel | Tu correo | Con él entrarás a `rosaacevedo.com/admin` |
| 2 | Contraseña del panel | Nueva, 12+ caracteres con mayúsculas, minúsculas y números (forma de ejemplo: `RaicesItagui2026Panel`) | La contraseña de ese usuario |
| 3 | Usuario del sitio cerrado | Una palabra simple, por ejemplo `campana` | El navegador lo pedirá a quien abra el sitio mientras está cerrado |
| 4 | Contraseña del sitio cerrado | Fácil de compartir con el equipo, por ejemplo `Siembra2026` | Va con el usuario anterior |

**Por qué cerrar el sitio al inicio:** la política de datos todavía es un borrador con `[POR CONFIRMAR]` y faltan textos de la campaña. Con la puerta con clave, el equipo revisa el sitio real en rosaacevedo.com sin que el público ni Google lo vean. Cuando todo esté aprobado, se quita (Parte 9).

**Cómo saber que terminaste:** tienes los 4 datos guardados. Sigue con la Parte 4.

---

## Parte 4 · GitHub: guardar los secretos

**Qué es:** el robot necesita los datos de Hostinger para entrar. Se guardan como **secretos**: GitHub los usa pero nunca los muestra, ni siquiera a ti después de guardarlos.

1. Abre el repositorio en GitHub → pestaña **Settings** (arriba a la derecha) → en el menú de la izquierda, **Secrets and variables** → **Actions**.
   - Ojo: dentro de «Secrets and variables» hay dos opciones, **Actions** y **Agents**. Usa **Actions**: esos secretos son los que lee el robot del despliegue. Los de «Agents» son para otra herramienta y el despliegue no los ve.
2. En la pestaña **Secrets**, botón verde **New repository secret**. Para cada fila de la tabla: en **Name** escribe el nombre exacto (en mayúsculas), en **Secret** pega el valor y clic en **Add secret**. Repite hasta tener los 11:

| Nombre del secreto | Qué pegar | De dónde sale |
|---|---|---|
| `HOSTINGER_SSH_HOST` | La IP, por ejemplo `123.45.67.89` | Paso 2.3 |
| `HOSTINGER_SSH_PORT` | `65002` | Paso 2.3 |
| `HOSTINGER_SSH_USER` | `u123456789` | Paso 2.3 |
| `HOSTINGER_SSH_KEY` | La llave **privada** completa (ver abajo) | Paso 2.4 |
| `DB_NOMBRE` | `u123456789_rosa` | Paso 2.2 |
| `DB_USUARIO` | `u123456789_rosa` | Paso 2.2 |
| `DB_CLAVE` | La contraseña de la base | Paso 2.2 |
| `PANEL_CORREO` | Dato 1: tu correo para entrar al panel | Parte 3 |
| `PANEL_CLAVE` | Dato 2: tu contraseña del panel | Parte 3 |
| `SITIO_USUARIO` | Dato 3: usuario del sitio cerrado | Parte 3 |
| `SITIO_CLAVE` | Dato 4: contraseña del sitio cerrado | Parte 3 |

   Para copiar la llave **privada**, en la Terminal de tu Mac:
   ```bash
   pbcopy < ~/.ssh/rosa_despliegue
   ```
   y pégala completa en `HOSTINGER_SSH_KEY`, incluidas las líneas `-----BEGIN OPENSSH PRIVATE KEY-----` y `-----END OPENSSH PRIVATE KEY-----`.

3. En la misma página, pestaña **Variables** → **New repository variable**:
   - Name: `URL_SITIO` · Value: `https://rosaacevedo.com` → **Add variable**.

**Cómo saber que salió bien:** en Settings → Secrets and variables → **Actions** ves 11 secretos en la pestaña Secrets y 1 variable en la pestaña Variables. GitHub no te deja volver a ver el valor de un secreto (solo cambiarlo): es normal.

---

## Parte 5 · Publicar: el pull request y el merge

1. Pídele a Claude: «crea el pull request hacia main». Te dará un enlace.
2. Ábrelo. Arriba verás **Conversation**, **Commits** y **Files changed**: ahí puedes ver todo lo que entra. Abajo, GitHub corre las pruebas; espera a que aparezca el visto verde **All checks have passed**.
3. Clic en **Merge pull request** → **Confirm merge**.
4. Ve a la pestaña **Actions** del repositorio. Verás «Despliegue a Hostinger» corriendo (círculo amarillo). Haz clic para seguirlo en vivo. Los pasos son:

| Paso en Actions | Qué está pasando |
|---|---|
| pruebas | Corre todas las pruebas del sitio |
| Construir | Descarga librerías y compila estilos |
| Llave SSH / Rutas en el servidor | Entra a Hostinger y ubica las carpetas |
| Subir versión | Copia el sitio a una carpeta nueva |
| Preparar el servidor | La primera vez: crea `.env`, carpetas y conecta el dominio |
| Activar versión | Actualiza la base de datos, carga datos base, crea tu usuario y cambia a la versión nueva |
| Instrucciones del cron | Escribe el comando que necesitas en la Parte 6 |
| Verificar que el sitio responde | Abre el sitio para comprobar |

**Cómo saber que salió bien:** visto verde en todo. Tarda unos 5 a 8 minutos.
**Si sale una X roja:** haz clic en el paso rojo, lee el mensaje (casi siempre dice qué secreto falta o qué está mal) y compártelo con Claude.

---

## Parte 6 · La tarea programada (cron)

**Qué es:** el sitio necesita que cada minuto alguien le diga «revisa si hay tareas»: enviar al CRM, publicar noticias programadas y sacar copias de seguridad. Eso lo hace el cron de Hostinger.

1. En GitHub → **Actions** → el despliegue que acaba de terminar → abajo, en **Summary**, aparece «Despliegue listo» con un comando como:
   ```
   cd /home/u123456789/rosaacevedo/current && /opt/alt/php83/usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```
2. hPanel → **Avanzado** → **Tareas Cron** → tipo **Personalizado** (Custom) → pega ese comando → frecuencia **cada minuto** (todos los campos con `*`) → Guardar.

**Cómo saber que salió bien:** la tarea aparece en la lista. Más adelante, en el panel → Sitio → Conexión con el CRM, verás que los envíos avanzan.

---

## Parte 7 · Dominio y candado (HTTPS)

1. hPanel → **Dominios** → rosaacevedo.com: debe decir que apunta a Hostinger. Si el dominio está registrado en otro lado, ahí te muestra los **nameservers** (o la IP) que debes poner donde lo compraste.
2. hPanel → **Seguridad** → **SSL** → instala el SSL gratuito para rosaacevedo.com.
3. Abre **https://rosaacevedo.com**. El navegador te pedirá **usuario y contraseña** (los del sitio cerrado, Parte 3). Debe aparecer el sitio con la rosa.

> Los cambios de DNS pueden tardar desde minutos hasta 24 horas. Si GitHub mostró un aviso amarillo en «Verificar que el sitio responde», casi siempre es esto: el despliegue sí quedó hecho.
>
> Cloudflare (que la especificación recomienda por seguridad y antispam) se puede agregar después, con calma, cuando el sitio ya funcione.

---

## Parte 8 · Tu primer ingreso al panel

1. Abre **https://rosaacevedo.com/admin** (primero el usuario del sitio cerrado, luego tu correo y contraseña del panel).
2. Te pedirá configurar el **doble factor**: instala en tu celular **Google Authenticator** o **Microsoft Authenticator**, escanea el código y escribe los 6 números. Guarda los códigos de recuperación.
3. Completa en el panel:
   - **Sitio → Configuración**: responsable del tratamiento de datos, correo de contacto, WhatsApp.
   - **Usuarios y roles**: crea las cuentas del equipo (cada persona configura su doble factor).
   - **Contenido**: banners, noticias, textos.

---

## Parte 9 · Abrir el sitio al público (cuando la campaña apruebe)

1. GitHub → Settings → Secrets and variables → **Actions** → **borra** los secretos `SITIO_USUARIO` y `SITIO_CLAVE` (ícono de la papelera).
2. GitHub → pestaña **Actions** → en la izquierda **Despliegue a Hostinger** → botón **Run workflow** → **Run workflow**.
3. Al terminar, el sitio queda abierto y visible para Google.

Para volver a cerrarlo, crea de nuevo esos dos secretos y repite el paso 2.

---

## Parte 10 · El día a día

- **Publicar cambios:** Claude trabaja en su rama; cuando quieras publicar, pides el pull request, lo revisas y haces **Merge**. El robot hace el resto.
- **Volver a la versión anterior** si algo sale mal (por SSH):
  ```bash
  ssh -i ~/.ssh/rosa_despliegue -p 65002 u123456789@123.45.67.89
  bash rosaacevedo/current/deploy/revertir.sh ~/rosaacevedo
  exit
  ```
- **Ver errores del sitio:** por SSH, `tail -n 50 ~/rosaacevedo/shared/storage/logs/laravel-$(date +%F).log` muestra las últimas 50 líneas del registro de hoy.

### Cómo está organizado el servidor

```
/home/u123456789/
├── rosaacevedo/
│   ├── current → releases/20261024-…    ← la versión que está funcionando
│   ├── releases/                         ← las últimas 5 versiones
│   └── shared/
│       ├── .env                          ← configuración privada. NO BORRAR
│       └── storage/                      ← fotos subidas, registros (logs), copias
└── domains/rosaacevedo.com/public_html → rosaacevedo/current/public
```

### Lo que nunca debes hacer

- **Borrar o editar a mano `shared/.env`** sin saber qué cambias. Ahí están `APP_KEY` y `CELULAR_HMAC_KEY`: si cambian, los celulares guardados quedan ilegibles y los registros se empiezan a duplicar.
- **Compartir la llave privada** (`~/.ssh/rosa_despliegue`). Si se filtra: bórrala en hPanel, crea otra (paso 2.4) y actualiza el secreto `HOSTINGER_SSH_KEY`.
- **Hacer Merge si las pruebas salen en rojo.**

### Problemas frecuentes

| Lo que ves | Qué significa | Qué hacer |
|---|---|---|
| Actions: «Faltan secretos de Hostinger» | Un secreto no existe o tiene otro nombre | Revisa la tabla de la Parte 4 (mayúsculas exactas) y que estén en **Secrets and variables → Actions**, no en «Agents» |
| Actions: «Permission denied (publickey)» | GitHub no puede entrar al servidor | La llave pública no está en hPanel, o `HOSTINGER_SSH_KEY` no se pegó completa |
| Al entrar por SSH desde tu Mac te pide «password» | El servidor rechazó la llave | Paso 2.5, apartado «Si te pide una contraseña» |
| Actions: «faltan los secretos DB_NOMBRE…» | Primera vez sin datos de la base | Crea `DB_NOMBRE`, `DB_USUARIO`, `DB_CLAVE` |
| Actions: «Access denied for user» | Usuario o contraseña de la base incorrectos | Revisa el paso 2.2. Como el `.env` ya se creó, hay que corregirlo en el servidor: pídeselo a Claude |
| Actions: «no encontré PHP 8.3» | El servidor no tiene PHP 8.3 activo | Paso 2.1 |
| Aviso: «no se pudo crear el usuario del panel» | La contraseña del panel no cumple las reglas | Cambia `PANEL_CLAVE` (12+ con mayúsculas, minúsculas y números) y vuelve a correr el despliegue (Parte 9, paso 2) |
| El navegador muestra la página de Hostinger | El dominio aún no apunta o falta el cambio de public_html | Espera el DNS; si persiste, comparte captura |
| Error 500 en el sitio | Algo falló al arrancar | Mira el log (Parte 10) y compártelo |
