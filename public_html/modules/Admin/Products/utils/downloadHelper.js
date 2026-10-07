/**
 * Helper utility to download product images in browser
 */

export function getFileExtension(url) {
  if (!url) return 'jpg';
  const cleanUrl = url.split('?')[0];
  const match = cleanUrl.match(/\.(jpg|jpeg|png|webp|gif|svg)$/i);
  return match ? match[1].toLowerCase() : 'jpg';
}

export function sanitizeFilename(name) {
  if (!name) return 'imagen-producto';
  return name
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // remove accents
    .replace(/[/\\?%*:|"<>]/g, '-')  // remove invalid filename characters
    .replace(/\s+/g, '-')             // replace spaces with hyphens
    .replace(/-+/g, '-')             // remove duplicate hyphens
    .trim();
}

export async function downloadProductImage(url, suggestedName = 'producto') {
  if (!url || url.includes('placeholder.jpg')) {
    alert('Este producto no tiene una imagen cargada.');
    return false;
  }

  const ext = getFileExtension(url);
  const baseName = sanitizeFilename(suggestedName);
  const filename = baseName.endsWith(`.${ext}`) ? baseName : `${baseName}.${ext}`;

  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const blob = await response.blob();
    const blobUrl = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    
    // Revoke after a short delay to ensure click processing
    setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
    return true;
  } catch (err) {
    console.warn('Fetch blob download failed, trying direct link fallback:', err);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    return true;
  }
}
