export async function uploadPdf(file, margins, categoryId) {
    const formData = new FormData();
    formData.append('pdf_file', file);
    formData.append('retail_margin', margins.retail);
    if (margins.wholesale) {
        formData.append('wholesale_margin', margins.wholesale);
    }
    if (categoryId) {
        formData.append('category_id', categoryId);
    }
    
    const res = await fetch('/api/admin/import/parse.php', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    });
    
    if (!res.ok) {
        const error = await res.json();
        throw new Error(error.error || 'Error en parse.php');
    }
    
    return await res.json();
}

export async function fetchPreviewPage(importId, page) {
    const res = await fetch(`/api/admin/import/preview.php?importId=${encodeURIComponent(importId)}&page=${page}`, {
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    if (!res.ok) {
        throw new Error('Error al obtener vista previa');
    }
    
    return await res.json();
}

export async function updateRow(importId, rowIndex, changes) {
    const res = await fetch('/api/admin/import/update-row.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ importId, rowIndex, changes })
    });
    
    if (!res.ok) {
        throw new Error('Error al actualizar fila');
    }
    return await res.json();
}

export async function confirmImport(importId, filename) {
    const res = await fetch('/api/admin/import/confirm.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ importId, filename })
    });
    
    if (!res.ok) {
        throw new Error('Error al confirmar importación');
    }
    return await res.json();
}
