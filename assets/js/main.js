// ============================================
// CALENDARIO — SELECCIÓN DE FECHA Y TURNO
// ============================================
document.addEventListener('DOMContentLoaded', function () {

    const diasDisponibles = document.querySelectorAll('.cal-disponible');
    const fechaInput      = document.getElementById('fecha-elegida');
    const turnosSeccion   = document.getElementById('turnos-seccion');
    const turnosLista     = document.getElementById('turnos-lista');
    const turnoInput      = document.getElementById('turno-elegido');
    const btnPaso2        = document.getElementById('btn-paso2');

    // Solo ejecutamos el calendario si estamos en el paso 2
    if (diasDisponibles.length) {

        diasDisponibles.forEach(function (dia) {
            dia.addEventListener('click', function () {
                diasDisponibles.forEach(d => d.classList.remove('cal-seleccionado'));
                this.classList.add('cal-seleccionado');
                const fecha = this.dataset.fecha;
                fechaInput.value = fecha;
                cargarTurnos(fecha);
            });
        });

        function cargarTurnos(fecha) {
            turnosSeccion.style.display = 'none';
            turnosLista.innerHTML       = '<p>Cargando horarios...</p>';
            turnoInput.value            = '';
            btnPaso2.style.display      = 'none';

            fetch('../actions/get-turnos.php?fecha=' + fecha)
                .then(function (respuesta) {
                    return respuesta.json();
                })
                .then(function (turnos) {
                    turnosLista.innerHTML = '';
                    turnosSeccion.style.display = 'block';

                    if (turnos.length === 0) {
                        turnosLista.innerHTML = '<p>No hay horarios disponibles para este día.</p>';
                        return;
                    }

                    turnos.forEach(function (turno) {
                        const btn = document.createElement('button');
                        btn.type        = 'button';
                        btn.className   = 'turno-btn';
                        btn.textContent = turno.etiqueta + ' — ' + turno.hora_formato;
                        btn.dataset.id  = turno.id;

                        btn.addEventListener('click', function () {
                            document.querySelectorAll('.turno-btn')
                                    .forEach(b => b.classList.remove('turno-activo'));
                            this.classList.add('turno-activo');
                            turnoInput.value       = this.dataset.id;
                            btnPaso2.style.display = 'block';
                        });

                        turnosLista.appendChild(btn);
                    });
                });
        }
    }

    // ============================================
    // MOSTRAR/OCULTAR CAMPO DE DIRECCIÓN
    // ============================================
    // Este bloque está fuera del if del calendario
    // para que funcione en el paso 1 aunque no haya días disponibles
    const radios         = document.querySelectorAll('input[name="modalidad"]');
    const campoDireccion = document.getElementById('campo-direccion');

    if (radios.length && campoDireccion) {

        // Revisamos el estado inicial por si la página recarga con domicilio seleccionado
        radios.forEach(function (radio) {
            if (radio.checked && radio.value === 'domicilio') {
                campoDireccion.style.display = 'flex';
            }
        });

        // Escuchamos cambios
        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                campoDireccion.style.display =
                    this.value === 'domicilio' ? 'flex' : 'none';
            });
        });
    }

});

// ============================================
// SELECTOR DE ESTRELLAS EN RESEÑAS
// ============================================
document.addEventListener('DOMContentLoaded', function () {

    const estrellas       = document.querySelectorAll('.estrella-seleccionable');
    const puntuacionInput = document.getElementById('puntuacion-input');

    if (!estrellas.length) return;

    estrellas.forEach(function (estrella) {

        estrella.addEventListener('mouseover', function () {
            const valor = parseInt(this.dataset.valor);
            estrellas.forEach(function (e) {
                e.classList.toggle('activa', parseInt(e.dataset.valor) <= valor);
            });
        });

        estrella.addEventListener('mouseout', function () {
            const valorActual = parseInt(puntuacionInput.value);
            estrellas.forEach(function (e) {
                e.classList.toggle('activa', parseInt(e.dataset.valor) <= valorActual);
            });
        });

        estrella.addEventListener('click', function () {
            puntuacionInput.value = this.dataset.valor;
        });
    });

});




// ============================================
// LIGHTBOX — GALERÍA
// ============================================
document.addEventListener('DOMContentLoaded', function () {

    // Creamos el lightbox dinámicamente en el HTML
    const lightbox = document.createElement('div');
    lightbox.id        = 'lightbox';
    lightbox.innerHTML = `
        <div id="lightbox-fondo"></div>
        <div id="lightbox-contenido">
            <button id="lightbox-cerrar">&times;</button>
            <img id="lightbox-img" src="" alt="">
            <p id="lightbox-descripcion"></p>
        </div>
    `;
    document.body.appendChild(lightbox);

    // Seleccionamos todos los items de la galería
    const items = document.querySelectorAll('.galeria-item');
    if (!items.length) return;

    items.forEach(function (item) {
        item.addEventListener('click', function () {
            const img         = this.querySelector('img');
            const descripcion = this.querySelector('.galeria-overlay-descripcion');

            document.getElementById('lightbox-img').src         = img.src;
            document.getElementById('lightbox-img').alt         = img.alt;
            document.getElementById('lightbox-descripcion').textContent =
                descripcion ? descripcion.textContent : '';

            document.getElementById('lightbox').classList.add('lightbox-activo');
            document.body.style.overflow = 'hidden'; // Evita scroll mientras está abierto
        });
    });

    // Cerrar con el botón X
    document.getElementById('lightbox-cerrar').addEventListener('click', cerrarLightbox);

    // Cerrar haciendo clic en el fondo oscuro
    document.getElementById('lightbox-fondo').addEventListener('click', cerrarLightbox);

    // Cerrar con la tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrarLightbox();
    });

    function cerrarLightbox() {
        document.getElementById('lightbox').classList.remove('lightbox-activo');
        document.body.style.overflow = ''; // Restauramos el scroll
    }

});