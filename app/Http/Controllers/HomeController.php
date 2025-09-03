<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GuestyService;

class HomeController extends Controller
{
    protected $guestyService;

    public function __construct(GuestyService $guestyService)
    {
        $this->guestyService = $guestyService;
    }

    /**
     * Mostrar la página de inicio
     */
   
     public function index()
     {
         try {
             // Obtener propiedades destacadas
             $featuredProperties = $this->guestyService->getListings([
                 'limit' => 4
             ]);
     
             // Obtener imágenes destacadas
             $featuredImages = $this->getFeaturedImages($featuredProperties['results'] ?? []);
     
             // Obtener la página con ID 1
             $paginaModel = \App\Models\Pagina::with('meta')->find(1);
     
             // Si no existe, crear instancias vacías
             if (!$paginaModel) {
                 $pagina = new \App\Models\Pagina();
                 $seo = new \App\Models\PaginaMeta();
             } else {
                 $pagina = $paginaModel;
                 $seo = $paginaModel->meta ?? new \App\Models\PaginaMeta();
             }
     
             return view('home', [
                 'featuredProperties' => $featuredProperties['results'] ?? [],
                 'featuredImages' => $featuredImages,
                 'pagina' => $pagina,
                 'seo' => $seo
             ]);
     
         } catch (\Exception $e) {
             \Log::error('Error al obtener propiedades:', ['error' => $e->getMessage()]);
     
             // Cargar contenido SEO y de página aunque haya error en Guesty
             $paginaModel = \App\Models\Pagina::with('meta')->find(1);
     
             if (!$paginaModel) {
                 $pagina = new \App\Models\Pagina();
                 $seo = new \App\Models\PaginaMeta();
             } else {
                 $pagina = $paginaModel;
                 $seo = $paginaModel->meta ?? new \App\Models\PaginaMeta();
             }
     
             return view('home', [
                 'featuredProperties' => [],
                 'featuredImages' => [],
                 'pagina' => $pagina,
                 'seo' => $seo
             ]);
         }
     }
     
     


    /**
     * Obtener imágenes de propiedades destacadas
     */
    private function getFeaturedImages(array $properties)
    {
        $images = [];
    
        foreach ($properties as $property) {
            if (isset($property['pictures']) && is_array($property['pictures'])) {
                foreach ($property['pictures'] as $picture) {
                    if (!empty($picture['original'])) {
                        $images[] = $picture['original'];
                    }
                }
            }
        }
    
        \Log::info('Imágenes obtenidas:', $images); // Ver imágenes en logs
        return $images;
    }
    
    

    /**
     * Mostrar la página Acerca de
     */
    public function about()
    {
        // Datos generales
        $pagina = \App\Models\Pagina::find(1);
    
        // Datos específicos de "Nosotros"
        $contenido = \App\Models\Pagina::find(3);
    
        return view('about', compact('pagina', 'contenido'));
    }
    

    /**
     * Mostrar la página de contacto
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Mostrar la página de servicios y experiencias
     */
    public function services()
    {
        return view('services');
    }

    /**
     * Mostrar la página para propietarios
     */
    public function forOwners()
    {
        return view('for-owners');
    }

    /**
     * Mostrar la página de FAQ
     */
    public function faq()
    {
        return view('faq');
    }
}