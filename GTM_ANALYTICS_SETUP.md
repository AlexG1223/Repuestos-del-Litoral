# Guía Completa de Configuración: GTM, GA4 Enhanced Ecommerce, Meta Pixel y Search Console

Esta documentación sirve como referencia técnica y guía de despliegue para la infraestructura de analítica y seguimiento de conversiones ecommerce en **Repuestos del Litoral** (repuestosdellitoral.com).

---

## 1. Arquitectura en el Código del Sitio

Todo el tracking de analítica está centralizado a través de un **único contenedor de Google Tag Manager (GTM)**. No existen scripts directos de GA4 ni Meta Pixel pegados en el código HTML.

### Archivos Creados y Modificados
- [`private/config/settings.php`](file:///c:/xampp/htdocs/repuestosDelLitoral/private/config/settings.php): Definición central de constantes de configuración (`GTM_CONTAINER_ID`, `GA4_MEASUREMENT_ID`, `META_PIXEL_ID`, `SEARCH_CONSOLE_VERIFICATION`).
- [`.env`](file:///c:/xampp/htdocs/repuestosDelLitoral/.env) y [`.env.example`](file:///c:/xampp/htdocs/repuestosDelLitoral/.env.example): Variables de entorno para configurar los IDs sin tocar código.
- [`public_html/includes/gtm-head.php`](file:///c:/xampp/htdocs/repuestosDelLitoral/public_html/includes/gtm-head.php): Inicialización de `dataLayer`, snippet `<script>` de GTM en el `<head>` y meta tag de verificación para Google Search Console.
- [`public_html/includes/gtm-body.php`](file:///c:/xampp/htdocs/repuestosDelLitoral/public_html/includes/gtm-body.php): Snippet `<noscript>` de GTM inyectado justo tras la apertura del `<body>` (vía `header.php`).
- [`public_html/modules/Analytics/analytics.js`](file:///c:/xampp/htdocs/repuestosDelLitoral/public_html/modules/Analytics/analytics.js): Módulo ES6 centralizado para `dataLayer.push()` aplicando reset obligatorio previo (`dataLayer.push({ ecommerce: null })`).

### Puntos de Interacción de Ecommerce
1. **`view_item`**: Se dispara automáticamente al cargar y renderizar la ficha de producto en [`useProductDetail.js`](file:///c:/xampp/htdocs/repuestosDelLitoral/public_html/modules/ProductDetail/hooks/useProductDetail.js).
2. **`add_to_cart`**: Se dispara en [`cartService.js`](file:///c:/xampp/htdocs/repuestosDelLitoral/public_html/modules/Cart/services/cartService.js) dentro de `addItem()`, capturando agregados desde la ficha de producto, catálogo o accesos rápidos.
3. **`begin_checkout`**: Se dispara al inicializar el formulario de pago en [`useCheckout.js`](file:///c:/xampp/htdocs/repuestosDelLitoral/public_html/modules/Checkout/hooks/useCheckout.js).
4. **`purchase`**: Se dispara al recibir la confirmación exitosa de creación del pedido en [`useCheckout.js`](file:///c:/xampp/htdocs/repuestosDelLitoral/public_html/modules/Checkout/hooks/useCheckout.js) enviando `transaction_id` (orderId), `value` (total) e `items`.

---

## 2. Configuración en la Consola Web de Google Tag Manager

Dentro de la consola web de Google Tag Manager (contenedor `GTM-XXXXXXX`), configurar los siguientes elementos:

### A. Variables de Capa de Datos (Data Layer Variables)
Crear 4 variables del tipo **Data Layer Variable**:
1. `dlv - ecommerce.value` -> Variable Name: `ecommerce.value`
2. `dlv - ecommerce.currency` -> Variable Name: `ecommerce.currency`
3. `dlv - ecommerce.transaction_id` -> Variable Name: `ecommerce.transaction_id`
4. `dlv - ecommerce.items` -> Variable Name: `ecommerce.items`

### B. Disparadores de Evento Personalizado (Custom Event Triggers)
Crear 4 disparadores de tipo **Custom Event**:
1. `ce - view_item` -> Event Name: `view_item`
2. `ce - add_to_cart` -> Event Name: `add_to_cart`
3. `ce - begin_checkout` -> Event Name: `begin_checkout`
4. `ce - purchase` -> Event Name: `purchase`

### C. Etiquetas de Google Analytics 4 (GA4)
1. **Etiqueta de Configuración GA4**:
   - Tipo: Google Tag / GA4 Configuration.
   - Tag ID: `G-SKQP4XXET0`.
   - Disparador: `All Pages`.
2. **GA4 Event - View Item**: Event Name: `view_item`, Disparador: `ce - view_item`.
3. **GA4 Event - Add To Cart**: Event Name: `add_to_cart`, Disparador: `ce - add_to_cart`.
4. **GA4 Event - Begin Checkout**: Event Name: `begin_checkout`, Disparador: `ce - begin_checkout`.
5. **GA4 Event - Purchase**: Event Name: `purchase`, Disparador: `ce - purchase`.

### D. Etiquetas de Meta Pixel
1. **Meta Pixel Base (PageView)**:
   - Script de Meta Pixel base apuntando a `META_PIXEL_ID`.
   - Disparador: `All Pages`.
2. **Meta Pixel - ViewContent**: Disparador `ce - view_item`, parámetros `{ value: {{dlv - ecommerce.value}}, currency: 'UYU' }`.
3. **Meta Pixel - AddToCart**: Disparador `ce - add_to_cart`, parámetros `{ value: {{dlv - ecommerce.value}}, currency: 'UYU' }`.
4. **Meta Pixel - InitiateCheckout**: Disparador `ce - begin_checkout`, parámetros `{ value: {{dlv - ecommerce.value}}, currency: 'UYU' }`.
5. **Meta Pixel - Purchase**: Disparador `ce - purchase`, parámetros `{ value: {{dlv - ecommerce.value}}, currency: 'UYU' }`.

---

## 3. Verificación en Google Search Console

Existen dos alternativas preparadas:
- **Opción A (Recomendada en código)**: Definir la variable `SEARCH_CONSOLE_VERIFICATION=código_provisto` en el archivo `.env`. El sistema inyectará automáticamente `<meta name="google-site-verification" content="...">` dentro de `<head>`.
- **Opción B (Registro DNS en Hostinger hPanel)**: Ingresar a hPanel -> Dominio -> Editor de Zona DNS -> Agregar un registro de tipo **TXT** con el nombre `@` (o la raíz del dominio) y el valor provisto por Search Console.

---

## 4. Checklist de Purga de Caché en Hostinger (Post-Deploy)

Cada vez que se suban cambios de código a producción a través de hPanel File Manager o FTP/SFTP:
1. Ingresar al **hPanel de Hostinger**.
2. Navegar a **Hosting** -> Administrar **repuestosdellitoral.com**.
3. Ir a la sección **Rendimiento / Flush Cache** o **LiteSpeed Cache**.
4. Hacer clic en **Purgar Todo (Purge All Cache)**.
5. Probar el sitio en una ventana de incógnito para confirmar que los cambios se reflejan de inmediato.

---

## 5. Protocolo de Validación y QA

1. **GTM Preview Mode**: Activar el modo vista previa en Tag Assistant (`tagassistant.google.com`) y verificar que la etiqueta GTM cargue en todas las páginas y que los 4 eventos (`view_item`, `add_to_cart`, `begin_checkout`, `purchase`) se disparen en los momentos exactos con sus datos.
2. **GA4 DebugView**: En el panel de administración de GA4, ingresar a **DebugView** y confirmar el ingreso de eventos con sus parámetros (`value`, `currency`, `items`, `transaction_id`).
3. **Meta Pixel Helper**: Con la extensión de Chrome de Meta Pixel Helper, verificar que `PageView`, `ViewContent`, `AddToCart`, `InitiateCheckout` y `Purchase` se emitan sin advertencias ni errores.
