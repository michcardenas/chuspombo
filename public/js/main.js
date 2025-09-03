// Función para manejar la navegación con scroll
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }
    
    // Inicializar datepickers si existen
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    
    if (checkinInput && checkoutInput) {
        // Establecer fecha mínima como hoy
        const today = new Date().toISOString().split('T')[0];
        checkinInput.min = today;
        
        // Actualizar fecha mínima de checkout cuando cambia checkin
        checkinInput.addEventListener('change', function() {
            checkoutInput.min = this.value;
            
            // Si checkout es menor que checkin, actualizar checkout
            if (checkoutInput.value && checkoutInput.value < this.value) {
                checkoutInput.value = this.value;
            }
        });
    }

    
    // Capturar todas las imágenes del carrusel
    let images = [];
        let currentIndex = 0;
        let modal = new bootstrap.Modal(document.getElementById('imageModal'));

        // Capturar imágenes del carrusel y abrir en el modal
        document.querySelectorAll(".carousel-img").forEach((img, index) => {
            img.addEventListener("click", function () {
                images = Array.from(document.querySelectorAll(".carousel-img")).map(img => img.getAttribute("data-bs-img"));
                currentIndex = index;
                updateModalImage();
                modal.show();
            });
        });

        // Actualiza la imagen del modal
        function updateModalImage() {
            let modalImage = document.getElementById("modalImage");
            modalImage.src = images[currentIndex];
            modalImage.classList.remove("zoomed");
        }

        // Navegar entre imágenes
        document.getElementById("prevImage").addEventListener("click", function (e) {
            e.stopPropagation();
            if (currentIndex > 0) {
                currentIndex--;
                updateModalImage();
            }
        });

        document.getElementById("nextImage").addEventListener("click", function (e) {
            e.stopPropagation();
            if (currentIndex < images.length - 1) {
                currentIndex++;
                updateModalImage();
            }
        });

        // Zoom en la imagen al hacer clic
        document.getElementById("modalImage").addEventListener("click", function () {
            this.classList.toggle("zoomed");
        });

        // Cerrar modal al hacer clic fuera de la imagen
        document.getElementById("imageModal").addEventListener("click", function (e) {
            if (e.target.id === "imageModal") {
                modal.hide();
            }
        });

        // Eliminar la capa negra al cerrar el modal
        document.getElementById('imageModal').addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('modal-open');
            document.querySelector('.modal-backdrop')?.remove();
        });



});


