import { uploadPdf, fetchPreviewPage, updateRow, confirmImport } from '../services/importService.js';
import { renderUploadForm } from '../components/ImportUploadForm.js';
import { renderRawPreview } from '../components/ImportRawPreview.js';
import { renderPreviewTable } from '../components/ImportPreviewTable.js';
import { renderSummary } from '../components/ImportSummary.js';

export function initImportApp(containerId) {
    let importId = null;
    let filename = '';
    
    const root = document.getElementById(containerId);
    if (!root) return;
    
    // Parse categories injected via data attribute
    let categories = [];
    try {
        const catData = root.getAttribute('data-categories');
        if (catData) categories = JSON.parse(catData);
    } catch (e) {}

    const state = {
        step: 'upload', // upload, raw, preview, loading, summary
        rawText: '',
        totalRows: 0,
        pageData: null,
        pageMeta: null,
        summary: null,
        error: null
    };

    function render() {
        if (state.error) {
            root.innerHTML = `<div class="admin-alert admin-alert-danger" style="margin-bottom:1rem">${state.error} <button onclick="window.location.reload()">Reintentar</button></div>`;
            return;
        }

        if (state.step === 'loading') {
            root.innerHTML = `
               <div style="text-align: center; padding: 4rem;">
                  <h3>Procesando...</h3>
                  <p>Por favor, no cierres esta ventana. Esto puede tardar unos minutos si el archivo es grande.</p>
               </div>
            `;
            return;
        }

        if (state.step === 'upload') {
            root.innerHTML = `<div id="upload-container"></div>`;
            renderUploadForm('upload-container', categories, handleUpload);
        } else if (state.step === 'raw') {
            root.innerHTML = `<div id="raw-container"></div>`;
            renderRawPreview('raw-container', state.rawText, state.totalRows, () => {
                state.step = 'preview';
                loadPreviewPage(1);
            });
        } else if (state.step === 'preview') {
            root.innerHTML = `<div id="preview-container"></div>`;
            renderPreviewTable('preview-container', state.pageData, state.pageMeta, loadPreviewPage, handleUpdateRow, handleConfirm);
        } else if (state.step === 'summary') {
            root.innerHTML = `<div id="summary-container"></div>`;
            renderSummary('summary-container', state.summary, () => {
                window.location.reload();
            });
        }
    }

    async function handleUpload(file, margins, categoryId) {
        state.step = 'loading';
        state.error = null;
        filename = file.name;
        render();

        try {
            const res = await uploadPdf(file, margins, categoryId);
            importId = res.data.importId;
            state.rawText = res.data.rawPreview;
            state.totalRows = res.data.totalRows;
            state.step = 'raw';
        } catch (err) {
            state.error = err.message;
        }
        render();
    }

    async function loadPreviewPage(page) {
        state.step = 'loading';
        render();

        try {
            const res = await fetchPreviewPage(importId, page);
            state.pageData = res.data;
            state.pageMeta = res.meta;
            state.step = 'preview';
        } catch (err) {
            state.error = err.message;
        }
        render();
    }

    async function handleUpdateRow(globalIndex, changes, trElement) {
        const btn = trElement.querySelector('.btn-save-row');
        btn.textContent = '⏳';
        btn.disabled = true;

        try {
            await updateRow(importId, globalIndex, changes);
            btn.textContent = '✅';
            if (changes.excluded) {
                trElement.classList.add('excluded');
            } else {
                trElement.classList.remove('excluded');
            }
        } catch (err) {
            alert(err.message);
            btn.textContent = '💾';
        } finally {
            btn.disabled = false;
        }
    }

    async function handleConfirm() {
        if (!confirm('¿Estás seguro de publicar estos productos en el catálogo?')) return;
        
        state.step = 'loading';
        render();

        try {
            const res = await confirmImport(importId, filename);
            state.summary = res.data;
            state.step = 'summary';
        } catch (err) {
            state.error = err.message;
        }
        render();
    }

    // Inicio
    render();
}
