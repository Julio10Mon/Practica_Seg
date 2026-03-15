
    function switchTab(evt, name) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('is-active'));
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('is-active'));
    document.getElementById(name).classList.add('is-active');
    evt.currentTarget.classList.add('is-active');
      // Actualiza la URL sin recargar para mantener la pestaña al refrescar
    window.history.pushState(null, null, `?tab=${name}`);
    }
    function showModal(id) { document.getElementById(id).style.display='block'; }
    function hideModal(id) { document.getElementById(id).style.display='none'; }
    window.onclick = function(e) { if(e.target.className === 'modal') e.target.style.display = 'none'; }
    function updateRange(el, id) {
    // Actualiza el texto del porcentaje
    document.getElementById('val_' + id).innerText = el.value + ' %';
    
    // Calcula el porcentaje para mover la línea verde
    const val = el.value;
    const min = el.min ? el.min : 0;
    const max = el.max ? el.max : 100;
    const pct = (val - min) * 100 / (max - min);
    
    // Aplica el tamaño al fondo del input
    el.style.backgroundSize = pct + '% 100%';
}

// Ejecutar al cargar para que las barras no aparezcan grises al inicio
document.querySelectorAll('.range-styled').forEach(el => {
    const pct = (el.value - el.min) * 100 / (el.max - el.min);
    el.style.backgroundSize = pct + '% 100%';
});
// Función para mostrar/ocultar el formulario al dar clic en "+ Agregar"
function toggleForm(id) {
    const form = document.getElementById(id);
    form.style.display = (form.style.display === 'none') ? 'block' : 'none';
}

// Función que ya tienes para actualizar la línea verde del slider
function updateRange(el, id) {
    document.getElementById('val_' + id).innerText = el.value + ' %';
    const pct = (el.value - el.min) * 100 / (el.max - el.min);
    el.style.backgroundSize = pct + '% 100%';
}


// Asegúrate de que este ID coincida con el input file de tu modal
const fileInput = document.getElementById('file_input');

if (fileInput) {
    fileInput.addEventListener('change', function() {
        const display = document.getElementById('file_name_display');
        const uploadText = document.getElementById('upload_text');
        const uploadIcon = document.getElementById('upload_icon');
        const dropZone = document.getElementById('drop_zone');
        
        if (this.files && this.files.length > 0) {
            const fileName = this.files[0].name;
            const fileSize = (this.files[0].size / 1024).toFixed(1); // Tamaño en KB
            
            // 1. Mostrar el nombre y tamaño
            display.innerHTML = `✅ ${fileName} (${fileSize} KB)`;
            
            // 2. Cambiar estilos visuales
            uploadText.innerHTML = "¡Archivo detectado!";
            uploadText.style.color = "#10b981"; 
            uploadIcon.innerHTML = "📂";
            dropZone.style.borderColor = "#8b5cf6";
            dropZone.style.background = "rgba(139, 92, 246, 0.1)";
            
            console.log("Archivo cargado: " + fileName); // Para debug en consola
        } else {
            // Resetear si se cancela la selección
            display.innerHTML = "";
            uploadText.innerHTML = "Subir certificado (PDF, PNG, JPG)";
            uploadText.style.color = "";
            uploadIcon.innerHTML = "📤";
            dropZone.style.background = "transparent";
        }
    });
}

document.getElementById('fileProy').addEventListener('change', function() {
    const file = this.files[0];
    const fileNameDisplay = document.getElementById('file-name');
    const uploadArea = document.getElementById('drop-zone');
    const uploadContent = document.getElementById('upload-content');

    if (file) {
        // Cambiamos el texto al nombre del archivo
        fileNameDisplay.innerText = "Archivo seleccionado: " + file.name;
        fileNameDisplay.style.color = "#10b981"; // Color verde éxito
        
        // Efecto visual en el contenedor
        uploadArea.style.borderColor = "#10b981";
        uploadArea.style.background = "rgba(16, 185, 129, 0.05)";
        
        // Opcional: Cambiar el icono a un check
        uploadContent.querySelector('span').innerText = "✅";
    } else {
        // Si se cancela la selección, volvemos al estado original
        fileNameDisplay.innerText = "Subir imagen";
        fileNameDisplay.style.color = "#94a3b8";
        uploadArea.style.borderColor = "#334155";
        uploadArea.style.background = "transparent";
        uploadContent.querySelector('span').innerText = "📤";
    }
});


// js/main.js

function eliminarTecnologia(id) {
    if (confirm('¿Estás seguro de eliminar este lenguaje?')) {
        // Redirige al PHP pasando el ID por la URL
        window.location.href = `perfil.php?delete_tech=${id}&tab=lenguajes`;
    }
}

function prepararEdicion(id, tecnologia_id, porcentaje) {
    // 1. Buscamos el formulario de la pestaña activa
    const activeTab = document.querySelector('.tab-content.is-active');
    const form = activeTab.querySelector('form'); 
    
    if (!form) return;

    // 2. Insertamos o actualizamos el ID de edición oculto
    let inputEdicion = document.getElementById('id_edicion');
    if (!inputEdicion) {
        inputEdicion = document.createElement('input');
        inputEdicion.type = 'hidden';
        inputEdicion.name = 'id_edicion';
        inputEdicion.id = 'id_edicion';
        form.appendChild(inputEdicion);
    }
    inputEdicion.value = id;

    // 3. Llenamos los campos del formulario
    const select = form.querySelector('select[name="tecnologia_id"]');
    const range = form.querySelector('input[name="porcentaje"]');
    
    if (select) select.value = tecnologia_id;
    if (range) {
        range.value = porcentaje;
        // Actualizar el texto del porcentaje (ej: val_lenguajes)
        const display = document.getElementById('val_lenguajes') || document.getElementById('val_hab');
        if (display) display.innerText = porcentaje + " %";
    }
    
    // 4. Cambiar el texto del botón para feedback
    const btnSubmit = form.querySelector('button[name="btn_agregar_tech"]');
    if (btnSubmit) {
        btnSubmit.innerText = "Actualizar Dominio";
        btnSubmit.classList.add('btn-update-mode'); // Opcional: para cambiar color por CSS
    }

    // 5. Scroll suave al formulario
    form.scrollIntoView({ behavior: 'smooth' });
}

function eliminarHabilidad(id) {
    if (confirm('¿Estás seguro de que quieres eliminar esta habilidad?')) {
        // Redirige pasando el ID y forzando la pestaña de habilidades
        window.location.href = 'perfil.php?delete_hab=' + id + '&tab=habilidades';
    }
}


// 2. ELIMINAR CERTIFICACIÓN
function eliminarCert(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este certificado?')) {
        // Redirige pasando el parámetro delete_cert y forzando la pestaña de certificaciones
        window.location.href = 'perfil.php?delete_cert=' + id + '&tab=certificaciones';
    }
}

// 3. ELIMINAR PROYECTO
function eliminarProj(id) {
    if (confirm('¿Estás seguro de eliminar este proyecto de tu portafolio?')) {
        // Redirige pasando el parámetro delete_proj y forzando la pestaña de proyectos
        window.location.href = 'perfil.php?delete_proj=' + id + '&tab=proyectos';
    }
}

/**
 * Función auxiliar para actualizar el texto de los rangos (Sliders)
 * Se usa en los formularios de Lenguajes y Habilidades
 */
function updateRange(el, suffix) {
    const valSpan = document.getElementById('val_' + suffix);
    if (valSpan) {
        valSpan.textContent = el.value + ' %';
    }
}

function showModal(id) {
    document.getElementById(id).style.display = 'block';
    document.body.style.overflow = 'hidden'; // DESACTIVA la barra de la página
}

function hideModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = 'auto'; // REACTIVA la barra de la página
}


function showModal(id) {
  const modal = document.getElementById(id);
  modal.style.display = "flex";
  document.body.classList.add("modal-open");
}

function hideModal(id) {
  const modal = document.getElementById(id);
  modal.style.display = "none";
  document.body.classList.remove("modal-open");
}

function updateRange(el) {
    // 1. Calcular el porcentaje actual
    const value = el.value;
    const min = el.min ? el.min : 0;
    const max = el.max ? el.max : 100;
    const percentage = (value - min) * 100 / (max - min);

    // 2. Aplicar la línea verde dinámicamente
    el.style.backgroundSize = percentage + '% 100%';

    // 3. Buscar el span del porcentaje para actualizar el texto
    // Buscamos dentro del mismo contenedor (form-group)
    const container = el.closest('.form-group');
    const display = container.querySelector('.skill-pct');
    if (display) {
        display.textContent = value + ' %';
    }
}

// ESTO ES VITAL: Ejecutar al cargar la página para que los que ya 
// tienen datos guardados muestren la línea verde desde el inicio.
document.addEventListener("DOMContentLoaded", function() {
    const allRanges = document.querySelectorAll('.range-styled');
    allRanges.forEach(range => {
        updateRange(range);
    });
});




