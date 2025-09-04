@extends('layouts.app')

@section('title', 'Reservar ' . ($property['title'] ?? 'Propiedad'))

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-7">
            <!-- Galería de imágenes -->
            <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach($property['pictures'] as $index => $picture)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $picture['original'] ?? asset('images/property-placeholder.jpg') }}" 
                                 class="d-block w-100 rounded carousel-img"
                                 alt="Imagen de la propiedad"
                                 data-bs-toggle="modal" data-bs-target="#imageModal"
                                 data-bs-img="{{ $picture['original'] }}">
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>

        <!-- Formulario de reserva -->
        <div class="col-md-5">
            <h1 class="fw-bold">{{ $property['title'] ?? 'Sin título' }}</h1>
            <p class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ $property['address']['full'] ?? 'Ubicación no disponible' }}</p>

            <div class="bg-light p-3 rounded">
                <h3 class="fw-bold">${{ $property['prices']['basePrice'] ?? 'N/A' }} <small class="text-muted">/ noche</small></h3>
                <p class="text-muted">Moneda: {{ $property['prices']['currency'] ?? 'USD' }}</p>
            </div>

            <h4 class="mt-4">Completa tu reserva</h4>
            <form action="{{ route('properties.reserve', $property['_id']) }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="guest_name" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="guest_name" name="guest_name" required>
                </div>

                <div class="mb-3">
                    <label for="guest_email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="guest_email" name="guest_email" required>
                </div>

                <div class="mb-3">
                    <label for="checkin_date" class="form-label">Fecha de Check-in</label>
                    <input type="date" class="form-control" id="checkin_date" name="checkin_date" required>
                </div>

                <div class="mb-3">
                    <label for="checkout_date" class="form-label">Fecha de Check-out</label>
                    <input type="date" class="form-control" id="checkout_date" name="checkout_date" required>
                </div>

                <div class="mb-3">
                    <label for="guests" class="form-label">Número de Huéspedes</label>
                    <input type="number" class="form-control" id="guests" name="guests" min="1" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Reservar Ahora</button>
            </form>

        </div>
    </div>
</div>

<!-- Modal para ampliar imágenes -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen"> 
        <div class="modal-content bg-transparent border-0"> 
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center position-relative">
                <button class="carousel-control-prev custom-control small-control" id="prevImage">
                    <span class="carousel-control-prev-icon"></span>
                </button>

                <img id="modalImage" src="" class="img-fluid rounded zoomable">

                <button class="carousel-control-next custom-control small-control" id="nextImage">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
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
</script>

<style>

    #modalImage {
        max-width: 90vw;
        max-height: 90vh;
        cursor: zoom-in;
        transition: transform 0.3s ease;
    }

    #modalImage.zoomed {
        transform: scale(1.5);
        cursor: zoom-out;
    }


</style>
@endsection
