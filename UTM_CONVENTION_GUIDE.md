# Guía Completa y Convención Fija de UTMs

Esta documentación establece la **convención fija e inmutable de parámetros UTM** para todos los enlaces compartidos por **Repuestos del Litoral** en canales digitales (Instagram, Facebook, WhatsApp, Email, Google Ads, TikTok, etc.).

El objetivo fundamental es **medir con precisión el CTR (Click-Through Rate) y la atribución de ventas por canal (OKRs: O1-KR2, O2-KR2)** sin fragmentación de datos en Google Analytics 4 (GA4).

---

## 1. La Taxonomía Estándar

Para evitar fragmentación (ejemplo: que en GA4 aparezca `Instagram`, `instagram`, `insta` o `IG` como 4 canales distintos), **todas las UTMs deben escribirse en minúsculas y seguir las reglas estrictas de esta tabla**:

| Parámetro | Definición | Regla de Formato | Valores Canónicos Permitidos |
| :--- | :--- | :--- | :--- |
| `utm_source` | Plataforma u origen del tráfico | Solo minúsculas, sin espacios | `instagram`, `facebook`, `whatsapp`, `google`, `email`, `tiktok`, `mercadolibre` |
| `utm_medium` | Tipo de canal / medio publicitario | Minúsculas, guiones `-` | `organic-social`, `paid-social`, `cpc`, `chat-organic`, `chat-automated`, `email-newsletter`, `bio-link` |
| `utm_campaign` | Nombre de la campaña u oferta | Minúsculas, guión bajo `_`. Estructura: `[mes]_[nombre]` | `setiembre_arranque`, `octubre_cyberlitoral`, `siempre_activo`, `cyber_monday` |
| `utm_content` | Pieza creativa, formato o botón específico | Minúsculas, guión bajo `_` | `historia_link_motosierra`, `post_carrusel_cadenas`, `ad_video_desbrozadora`, `boton_bio`, `link_catalogo` |
| `utm_term` | Palabra clave (opcional para pauta) | Minúsculas, guiones `-` | `motosierra-stihl`, `repuestos-desbrozadora` |

---

## 2. Matriz de Ejemplos según Canal de Difusión

### A. Instagram
- **Link en la Bio (Perfil):**
  `https://repuestosdellitoral.com/?utm_source=instagram&utm_medium=bio-link&utm_campaign=siempre_activo&utm_content=boton_bio`
- **Historia con Sticker de Enlace:**
  `https://repuestosdellitoral.com/motosierra-a-nafta.php?utm_source=instagram&utm_medium=organic-social&utm_campaign=setiembre_arranque&utm_content=historia_link_motosierra`
- **Post / Feed (Remitiendo al link de la bio o web):**
  `https://repuestosdellitoral.com/?utm_source=instagram&utm_medium=organic-social&utm_campaign=setiembre_arranque&utm_content=post_carrusel_ofertas`

### B. Meta Ads (Pauta Paga en Instagram / Facebook)
- **Anuncio en Feed / Stories:**
  `https://repuestosdellitoral.com/producto.php?id=123&utm_source=facebook&utm_medium=paid-social&utm_campaign=setiembre_arranque&utm_content=ad_video_cadena`

### C. WhatsApp (Atención al Cliente y Difusiones)
- **Catálogo o Link enviado en chat:**
  `https://repuestosdellitoral.com/?utm_source=whatsapp&utm_medium=chat-organic&utm_campaign=setiembre_arranque&utm_content=mensaje_directo_asesor`
- **Link en Estado de WhatsApp:**
  `https://repuestosdellitoral.com/?utm_source=whatsapp&utm_medium=organic-social&utm_campaign=setiembre_arranque&utm_content=estado_whatsapp`

---

## 3. Herramienta Interna: Generador de UTMs

Para facilitar el trabajo y asegurar que nadie cometa errores manuales, el sistema cuenta con un **Generador UTM integrado en el Panel de Administración**:

📌 **Acceso:** Panel Admin -> Menú Lateral -> `🔗 Generador UTM` (`/admin/utm-builder.php`).

**Características:**
1. **Botones de Presets Rápido:** Con un clic configura `Source` y `Medium` (Instagram Bio, Historia, Meta Ad, WhatsApp, etc.).
2. **Auto-Sanitizado:** Convierte automáticamente mayúsculas a minúsculas, elimina espacios accidentales y remueve caracteres especiales.
3. **Copia en 1 Clic:** Copia el enlace final listo para pegar en Instagram/Meta.

---

## 4. Cómo Captura GA4 y GTM estos Parámetros

1. **Captura Automática de Sesión (GA4):**
   Al ingresar un usuario desde una URL con UTMs, Google Analytics 4 atribuye automáticamente la sesión actual a la fuente y medio indicados.

2. **Persistencia en el Carrito y Checkout (`analytics.js`):**
   El código del sitio captura las UTMs de la primera página de entrada y las guarda en `sessionStorage`. Cuando el cliente completa una compra (`purchase`), la atribución se adjunta al evento ecommerce.

---

## 5. Consulta de Reportes de Atribución en GA4 (Medición de OKRs)

Para evaluar el progreso de **O1-KR2** (CTR y Tráfico por campaña) y **O2-KR2** (Ingresos y Conversiones por canal):

1. **Ver Tráfico por Fuente/Medio y Campaña:**
   - Ir a **GA4** -> **Informes** -> **Adquisición** -> **Adquisición de Tráfico**.
   - Cambiar la dimensión principal a `Fuente / medio de la sesión` o `Campaña de la sesión`.
   - Evaluar métricas: *Usuarios*, *Sesiones con interacción*, *Tasa de interacción* (CTR).

2. **Ver Atribución de Ventas e Ingresos:**
   - Ir a **GA4** -> **Publicidad** -> **Rutas de conversión** o **Comparación de modelos**.
   - O en **Explorar** -> Crear un informe de formato libre con dimensiones `Campaña de la sesión` y métricas `Ingresos totales` y `Compras de ecommerce`.
