-- Sitio oficial Rosa Alcaldesa 2027 · Modelo de datos de referencia
-- MySQL 8.0+ / MariaDB 10.6+ (Hostinger) · utf8mb4
-- Las migraciones de Laravel se escriben a partir de este script; las tablas propias de
-- Laravel (users, sessions, jobs, failed_jobs, cache, password_reset_tokens) y de los paquetes
-- spatie/laravel-permission (roles, permissions…), spatie/laravel-medialibrary (media) y
-- spatie/laravel-activitylog (activity_log) se crean con sus propias migraciones.

SET NAMES utf8mb4;

-- ---------------------------------------------------------------- Territorio
CREATE TABLE territorio_comuna (
  id            SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  division      ENUM('2024','2007') NOT NULL COMMENT '2024 = Acuerdo 017 (7 comunas + corregimiento); 2007 = división anterior (JAL 2027)',
  codigo        VARCHAR(10)  NOT NULL COMMENT 'C01…C07, CORR',
  nombre        VARCHAR(80)  NOT NULL,
  tipo          ENUM('comuna','corregimiento') NOT NULL,
  slug          VARCHAR(80)  NULL,
  area_ha       DECIMAL(8,2) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_comuna (division, codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE territorio_barrio (
  id               SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre           VARCHAR(120) NOT NULL,
  tipo             ENUM('barrio','sector','vereda') NOT NULL DEFAULT 'barrio',
  comuna_2024_id   SMALLINT UNSIGNED NOT NULL,
  comuna_2007_id   SMALLINT UNSIGNED NULL COMMENT 'Correspondencia con la división anterior',
  codigo_externo   VARCHAR(20) NULL COMMENT 'Código compartido con el CRM y la Encuesta Itagüí 2026',
  activo           TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_barrio (comuna_2024_id, nombre),
  CONSTRAINT fk_barrio_c24 FOREIGN KEY (comuna_2024_id) REFERENCES territorio_comuna(id),
  CONSTRAINT fk_barrio_c07 FOREIGN KEY (comuna_2007_id) REFERENCES territorio_comuna(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Configuración y sitio
CREATE TABLE ajustes (
  clave       VARCHAR(80) NOT NULL,
  valor       JSON NULL,
  grupo       VARCHAR(40) NOT NULL DEFAULT 'general',
  updated_by  BIGINT UNSIGNED NULL,
  updated_at  TIMESTAMP NULL,
  PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='modo_sitio, aviso_global, whatsapp_numero, whatsapp_mensaje, responsable_tratamiento, contacto_email, umami_id, turnstile_sitekey…';

CREATE TABLE menu_items (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ubicacion   ENUM('principal','pie_sitio','pie_transparencia') NOT NULL,
  texto       VARCHAR(60) NOT NULL,
  url         VARCHAR(255) NOT NULL,
  orden       SMALLINT NOT NULL DEFAULT 0,
  activo      TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY ix_menu (ubicacion, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE redes_sociales (
  id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(40) NOT NULL,
  url         VARCHAR(255) NOT NULL,
  icono       VARCHAR(40) NOT NULL,
  orden       SMALLINT NOT NULL DEFAULT 0,
  activa      TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE redirecciones (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  desde       VARCHAR(255) NOT NULL,
  hacia       VARCHAR(255) NOT NULL,
  codigo      SMALLINT NOT NULL DEFAULT 301,
  visitas     INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_desde (desde)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Contenido
CREATE TABLE ejes (
  id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(80) NOT NULL,
  articulo    VARCHAR(20) NOT NULL COMMENT 'la, las, los, una ciudad que',
  sujeto      VARCHAR(60) NOT NULL,
  icono       VARCHAR(40) NOT NULL COMMENT 'Nombre Lucide',
  frase       VARCHAR(200) NULL,
  contenido   JSON NULL COMMENT 'Bloques del Builder',
  compromisos JSON NULL,
  orden       SMALLINT NOT NULL DEFAULT 0,
  publicado   TINYINT(1) NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_eje_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE paginas (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(120) NOT NULL,
  titulo      VARCHAR(160) NOT NULL,
  bloques     JSON NULL,
  seo_titulo  VARCHAR(70) NULL,
  seo_descripcion VARCHAR(160) NULL,
  estado      ENUM('borrador','publicada','archivada') NOT NULL DEFAULT 'borrador',
  created_at  TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pagina_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comuna_paginas (
  id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  comuna_id   SMALLINT UNSIGNED NOT NULL,
  saludo      TEXT NULL,
  bloques     JSON NULL,
  codigo_whatsapp VARCHAR(30) NULL,
  publicada   TINYINT(1) NOT NULL DEFAULT 0,
  updated_at  TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_comuna_pagina (comuna_id),
  CONSTRAINT fk_cp_comuna FOREIGN KEY (comuna_id) REFERENCES territorio_comuna(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE banners (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pagina          VARCHAR(40) NOT NULL DEFAULT 'inicio' COMMENT 'inicio | comuna',
  comuna_id       SMALLINT UNSIGNED NULL,
  etiqueta        VARCHAR(60) NULL,
  titular         VARCHAR(70) NOT NULL,
  texto           VARCHAR(160) NULL,
  mostrar_lema    TINYINT(1) NOT NULL DEFAULT 0,
  btn1_texto      VARCHAR(30) NULL, btn1_url VARCHAR(255) NULL,
  btn2_texto      VARCHAR(30) NULL, btn2_url VARCHAR(255) NULL,
  alt             VARCHAR(200) NOT NULL COMMENT 'Texto alternativo obligatorio; imágenes en media (colecciones escritorio y movil)',
  variante        CHAR(1) NULL COMMENT 'A, B… para prueba A/B',
  orden           SMALLINT NOT NULL DEFAULT 0,
  publicar_desde  DATETIME NULL,
  publicar_hasta  DATETIME NULL,
  estado          ENUM('borrador','activo','archivado') NOT NULL DEFAULT 'borrador',
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY ix_banner_vigente (pagina, comuna_id, estado, orden),
  CONSTRAINT fk_banner_comuna FOREIGN KEY (comuna_id) REFERENCES territorio_comuna(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE noticias (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug            VARCHAR(160) NOT NULL,
  titulo          VARCHAR(90) NOT NULL,
  resumen         VARCHAR(300) NULL,
  cuerpo          MEDIUMTEXT NULL,
  eje_id          SMALLINT UNSIGNED NULL,
  autor_id        BIGINT UNSIGNED NULL,
  estado          ENUM('borrador','revision','programada','publicada','archivada') NOT NULL DEFAULT 'borrador',
  publicada_en    DATETIME NULL,
  video_url       VARCHAR(255) NULL,
  seo_titulo      VARCHAR(70) NULL,
  seo_descripcion VARCHAR(160) NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_noticia_slug (slug),
  KEY ix_noticia_pub (estado, publicada_en),
  CONSTRAINT fk_noticia_eje FOREIGN KEY (eje_id) REFERENCES ejes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE noticia_comuna (
  noticia_id  INT UNSIGNED NOT NULL,
  comuna_id   SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (noticia_id, comuna_id),
  CONSTRAINT fk_nc_noticia FOREIGN KEY (noticia_id) REFERENCES noticias(id) ON DELETE CASCADE,
  CONSTRAINT fk_nc_comuna FOREIGN KEY (comuna_id) REFERENCES territorio_comuna(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE eventos (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(160) NOT NULL,
  titulo      VARCHAR(120) NOT NULL,
  descripcion TEXT NULL,
  tipo        ENUM('encuentro','recorrido','foro','reunion','otro') NOT NULL DEFAULT 'encuentro',
  inicia_en   DATETIME NOT NULL,
  termina_en  DATETIME NULL,
  lugar       VARCHAR(160) NULL,
  direccion   VARCHAR(200) NULL,
  lat         DECIMAL(9,6) NULL, lng DECIMAL(9,6) NULL,
  comuna_id   SMALLINT UNSIGNED NULL,
  cupo        INT UNSIGNED NULL,
  publico     TINYINT(1) NOT NULL DEFAULT 1,
  estado      ENUM('borrador','publicado','cancelado','realizado') NOT NULL DEFAULT 'borrador',
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_evento_slug (slug),
  KEY ix_evento_fecha (estado, inicia_en),
  CONSTRAINT fk_evento_comuna FOREIGN KEY (comuna_id) REFERENCES territorio_comuna(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enlaces_bio (
  id      SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  texto   VARCHAR(60) NOT NULL,
  url     VARCHAR(255) NOT NULL,
  icono   VARCHAR(40) NULL,
  orden   SMALLINT NOT NULL DEFAULT 0,
  activo  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Políticas y ciudadanía
CREATE TABLE politicas (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo            ENUM('tratamiento_datos','autorizacion_general','whatsapp','afinidad_politica','publicar_propuesta','cookies','uso_ia') NOT NULL,
  version         VARCHAR(10) NOT NULL,
  texto           MEDIUMTEXT NOT NULL,
  hash_sha256     CHAR(64) NOT NULL COMMENT 'Huella del texto exacto aceptado',
  vigente_desde   DATETIME NOT NULL,
  vigente         TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_politica (tipo, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ciudadanos (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid               CHAR(36) NOT NULL,
  celular_hash       CHAR(64) NOT NULL COMMENT 'HMAC-SHA256 del celular E.164: llave de deduplicación',
  celular_cifrado    VARBINARY(255) NOT NULL COMMENT 'Cifrado con APP_KEY (cast encrypted de Laravel)',
  nombre             VARCHAR(120) NOT NULL,
  email              VARCHAR(160) NULL,
  barrio_id          SMALLINT UNSIGNED NULL,
  paso_alcanzado     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  es_voluntario      TINYINT(1) NOT NULL DEFAULT 0,
  primer_origen      JSON NULL COMMENT 'utm_*, codigo_q, pagina, variante del primer contacto',
  estado             ENUM('activo','inactivo','retirado') NOT NULL DEFAULT 'activo' COMMENT 'retirado = revocó autorización',
  crm_id             VARCHAR(64) NULL,
  crm_sync_estado    ENUM('pendiente','sincronizado','error') NOT NULL DEFAULT 'pendiente',
  crm_sync_en        DATETIME NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ciudadano_uuid (uuid),
  UNIQUE KEY uq_ciudadano_celular (celular_hash),
  KEY ix_ciudadano_barrio (barrio_id),
  KEY ix_ciudadano_sync (crm_sync_estado),
  CONSTRAINT fk_ciudadano_barrio FOREIGN KEY (barrio_id) REFERENCES territorio_barrio(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE consentimientos (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ciudadano_id    BIGINT UNSIGNED NOT NULL,
  politica_id     SMALLINT UNSIGNED NOT NULL COMMENT 'Tipo y versión exacta del texto mostrado',
  otorgado        TINYINT(1) NOT NULL COMMENT '1 = otorga, 0 = revoca',
  formulario      VARCHAR(40) NOT NULL COMMENT 'registro_p1, buzon, evento, qr, titular…',
  ip              VARBINARY(16) NOT NULL,
  user_agent      VARCHAR(255) NULL,
  url             VARCHAR(255) NULL,
  created_at      TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  KEY ix_consent_ciudadano (ciudadano_id, politica_id, created_at),
  CONSTRAINT fk_consent_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(id),
  CONSTRAINT fk_consent_politica FOREIGN KEY (politica_id) REFERENCES politicas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Solo inserción: nunca UPDATE ni DELETE. El estado vigente es el último registro por política.';

CREATE TABLE voluntariado (
  ciudadano_id      BIGINT UNSIGNED NOT NULL,
  intereses         JSON NULL COMMENT 'vecinos, redes, eventos, testigo, transporte…',
  disponibilidad    JSON NULL COMMENT 'dias y franjas',
  puesto_votacion   VARCHAR(160) NULL COMMENT 'Solo si la persona lo comparte',
  updated_at        TIMESTAMP NULL,
  PRIMARY KEY (ciudadano_id),
  CONSTRAINT fk_vol_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enlaces_cortos (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo      VARCHAR(20) NOT NULL,
  destino     VARCHAR(255) NOT NULL,
  pieza       VARCHAR(120) NULL COMMENT 'Volante, valla, video, post…',
  comuna_id   SMALLINT UNSIGNED NULL,
  evento_id   INT UNSIGNED NULL,
  clics       INT UNSIGNED NOT NULL DEFAULT 0,
  activo      TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_codigo (codigo),
  CONSTRAINT fk_ec_comuna FOREIGN KEY (comuna_id) REFERENCES territorio_comuna(id),
  CONSTRAINT fk_ec_evento FOREIGN KEY (evento_id) REFERENCES eventos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE interacciones (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ciudadano_id    BIGINT UNSIGNED NULL,
  visitante_id    CHAR(36) NULL COMMENT 'Cookie de primera parte para A/B',
  tipo            VARCHAR(40) NOT NULL COMMENT 'registro_paso_1/2/3, propuesta, asistencia, whatsapp_clic, qr…',
  variante        VARCHAR(10) NULL,
  pagina          VARCHAR(255) NULL,
  comuna_pagina_id SMALLINT UNSIGNED NULL,
  utm_source VARCHAR(80) NULL, utm_medium VARCHAR(80) NULL, utm_campaign VARCHAR(120) NULL, utm_content VARCHAR(120) NULL,
  codigo_q        VARCHAR(20) NULL,
  referrer        VARCHAR(255) NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_int_ciudadano (ciudadano_id),
  KEY ix_int_tipo_fecha (tipo, created_at),
  KEY ix_int_variante (variante, tipo),
  CONSTRAINT fk_int_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE temas (
  id      SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre  VARCHAR(60) NOT NULL,
  eje_id  SMALLINT UNSIGNED NULL,
  orden   SMALLINT NOT NULL DEFAULT 0,
  activo  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  CONSTRAINT fk_tema_eje FOREIGN KEY (eje_id) REFERENCES ejes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE propuestas_ciudadanas (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo             VARCHAR(16) NOT NULL COMMENT 'PR-AAAA-NNNN',
  ciudadano_id       BIGINT UNSIGNED NOT NULL,
  tema_id            SMALLINT UNSIGNED NOT NULL COMMENT 'Tema elegido por el ciudadano',
  tema_confirmado_id SMALLINT UNSIGNED NULL COMMENT 'Tema confirmado por el moderador',
  barrio_id          SMALLINT UNSIGNED NULL,
  texto              TEXT NOT NULL,
  publicar_anonima   TINYINT(1) NOT NULL DEFAULT 0,
  estado             ENUM('recibida','en_revision','incorporada','respondida','descartada') NOT NULL DEFAULT 'recibida',
  sugerencia_ia      JSON NULL COMMENT 'tema, comuna, sentimiento, resumen, modelo, fecha',
  asignada_a         BIGINT UNSIGNED NULL,
  nota_interna       TEXT NULL,
  respuesta          TEXT NULL,
  destacada          TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_prop_codigo (codigo),
  KEY ix_prop_estado (estado, created_at),
  KEY ix_prop_tema (tema_id),
  CONSTRAINT fk_prop_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(id),
  CONSTRAINT fk_prop_tema FOREIGN KEY (tema_id) REFERENCES temas(id),
  CONSTRAINT fk_prop_tema_conf FOREIGN KEY (tema_confirmado_id) REFERENCES temas(id),
  CONSTRAINT fk_prop_barrio FOREIGN KEY (barrio_id) REFERENCES territorio_barrio(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE asistencias_evento (
  evento_id     INT UNSIGNED NOT NULL,
  ciudadano_id  BIGINT UNSIGNED NOT NULL,
  estado        ENUM('inscrito','asistio','cancelo') NOT NULL DEFAULT 'inscrito',
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (evento_id, ciudadano_id),
  CONSTRAINT fk_asis_evento FOREIGN KEY (evento_id) REFERENCES eventos(id),
  CONSTRAINT fk_asis_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE solicitudes_titular (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  radicado       VARCHAR(16) NOT NULL,
  ciudadano_id   BIGINT UNSIGNED NULL,
  tipo           ENUM('consulta','actualizacion','supresion','revocatoria','reclamo') NOT NULL,
  nombre         VARCHAR(120) NOT NULL,
  contacto       VARCHAR(160) NOT NULL,
  detalle        TEXT NULL,
  vence_en       DATE NOT NULL COMMENT '10 días hábiles consultas; 15 reclamos',
  estado         ENUM('abierta','en_tramite','respondida','cerrada') NOT NULL DEFAULT 'abierta',
  respuesta      TEXT NULL,
  atendida_por   BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_radicado (radicado),
  KEY ix_sol_vence (estado, vence_en),
  CONSTRAINT fk_sol_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Integración con el CRM
CREATE TABLE crm_outbox (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  entidad       VARCHAR(40) NOT NULL COMMENT 'ciudadano, consentimiento, propuesta, asistencia, interaccion',
  entidad_id    BIGINT UNSIGNED NOT NULL,
  evento        VARCHAR(40) NOT NULL COMMENT 'creado, actualizado, revocado',
  payload       JSON NOT NULL,
  idempotencia  CHAR(36) NOT NULL,
  intentos      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  estado        ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  ultimo_error  VARCHAR(500) NULL,
  proximo_intento DATETIME NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_idem (idempotencia),
  KEY ix_outbox (estado, proximo_intento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Datos semilla: territorio 2024
INSERT INTO territorio_comuna (division, codigo, nombre, tipo, slug, area_ha) VALUES
 ('2024','C01','Comuna 1 · Centro','comuna','comuna-1-centro',279),
 ('2024','C02','Comuna 2 · Yarumito · Santa Ana','comuna','comuna-2',196),
 ('2024','C03','Comuna 3 · Ditaires · San Francisco','comuna','comuna-3',282),
 ('2024','C04','Comuna 4 · Santa María','comuna','comuna-4-santa-maria',320),
 ('2024','C05','Comuna 5 · Calatrava · El Tablazo','comuna','comuna-5',62),
 ('2024','C06','Comuna 6 · Fátima · La Unión','comuna','comuna-6',79),
 ('2024','C07','Comuna 7 · Del Valle · El Porvenir','comuna','comuna-7',154),
 ('2024','CORR','Corregimiento El Manzanillo','corregimiento','el-manzanillo',593);

INSERT INTO ejes (slug, articulo, sujeto, icono, frase, orden, publicado) VALUES
 ('salud','la','salud','heart-pulse','Red de salud cercana: citas, especialistas y atención en el barrio.',1,1),
 ('familias','las','familias','users','Que cada hogar viva mejor y con tranquilidad.',2,1),
 ('jovenes','los','jóvenes','graduation-cap','Que ningún joven tenga que irse de Itagüí para cumplir sus sueños.',3,1),
 ('comerciantes','los','comerciantes','store','Que el negocio de barrio vuelva a respirar.',4,1),
 ('seguridad','la','seguridad','shield','Que el miedo deje de caminar primero.',5,1),
 ('oportunidades','las','oportunidades','trending-up','Empleo cerca de casa y emprendimiento con respaldo.',6,1),
 ('ciudad-que-avanza','una ciudad que','avanza','sprout','Movilidad, espacio público y una ciudad moderna.',7,1);

INSERT INTO ajustes (clave, valor, grupo) VALUES
 ('modo_sitio','"precampana"','sitio'),
 ('aviso_global','{"activo":true,"texto":"Escucha ciudadana abierta: deja tu propuesta para tu barrio","url":"/buzon"}','sitio'),
 ('whatsapp_numero','"57XXXXXXXXXX"','redes'),
 ('whatsapp_canal','"https://whatsapp.com/channel/0029Vb6auV20QeanaeczZw2g"','redes'),
 ('whatsapp_mensaje','"Hola, quiero saber más de Rosa. Código: {codigo}"','redes'),
 ('responsable_tratamiento','{"nombre":"[por definir]","identificacion":"[NIT o cédula]","email":"datos@rosaacevedo.com"}','legal');

INSERT INTO redes_sociales (nombre, url, icono, orden, activa) VALUES
 ('Facebook','https://www.facebook.com/share/19mmqrNJD9/','facebook',1,1),
 ('Instagram','https://www.instagram.com/rosaacevedoj/','instagram',2,1),
 ('TikTok','https://www.tiktok.com/@rosaacevedoj','tiktok',3,1),
 ('X','https://x.com/rosaacevedoj','x',4,1);
